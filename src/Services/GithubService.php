<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Services;

use Github\AuthMethod;
use Github\Client;
use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Helper\DevtoolsHelper;
use Symfony\Component\Process\Process;
use Windwalker\Console\IO;
use Windwalker\Environment\Environment;
use Windwalker\Environment\PlatformHelper;
use Windwalker\Filesystem\FileObject;
use Windwalker\Http\Helper\ResponseHelper;

/**
 * The GithubService class.
 */
class GithubService
{
    protected const CLIENT_ID = '9935f1864afade172e5d';

    protected ?Client $client = null;

    /**
     * GithubService constructor.
     */
    public function __construct(
        protected Environment $environment,
        protected Application $app,
        protected EnvService $envService
    ) {
        $this->client = $this->getClient();
    }

    /**
     * tokenFile
     *
     * @return  FileObject
     *
     * @since  __DEPLOY_VERSION__
     */
    public function tokenFile(): FileObject
    {
        $home = $this->envService->getUserDir();

        return new FileObject($home . '/.lyra/github-token');
    }

    /**
     * getStoredToken
     *
     * @return  string|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getStoredToken(): ?string
    {
        $tokenFile = $this->tokenFile();

        if (is_file($tokenFile->getPathname())) {
            return trim(file_get_contents($tokenFile->getPathname()));
        }

        return null;
    }

    /**
     * generateToken
     *
     * @param  IO  $io
     *
     * @return  string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function generateToken(IO $io): string
    {
        $tokenFile = $this->tokenFile();

        $token = $this->deviceAuth($io);

        file_put_contents($tokenFile->getPathname(), $token);

        return $token;
    }

    /**
     * deviceAuth
     *
     * @param  IO  $io
     *
     * @return  string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @since  __DEPLOY_VERSION__
     */
    public function deviceAuth(IO $io): string
    {
        $http = new \GuzzleHttp\Client();
        $res = $http->post(
            'https://github.com/login/device/code',
            [
                'body' => json_encode(
                    [
                        'client_id' => static::CLIENT_ID,
                        'scope' => 'repo,write:public_key',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $data = json_decode((string) $res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $io->out("Please fill: <info>{$data['user_code']}</info> to github.com.");

        $this->openBrowser($data['verification_uri'], $io);

        $io->out('Now waiting GitHub response...');

        $total = 0;

        while (!$token = $this->accessToken($data['device_code'])) {
            if ($total > 150) {
                throw new \RuntimeException('Please enter code in 150 seconds.');
            }

            $total += 6;
            sleep(6);
        }

        return $token;
    }

    /**
     * checkCode
     *
     * @param  string  $deviceCode
     *
     * @return  string|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function accessToken(string $deviceCode): ?string
    {
        $endpoiint = 'https://github.com/login/oauth/access_token';

        $http = new \GuzzleHttp\Client();
        // @see https://docs.github.com/en/free-pro-team@latest/developers/apps/authorizing-oauth-apps#device-flow
        $res = $http->post(
            $endpoiint,
            [
                'body' => json_encode(
                    [
                        'client_id' => static::CLIENT_ID,
                        'device_code' => $deviceCode,
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        if (!ResponseHelper::isSuccess($res->getStatusCode())) {
            return null;
        }

        $data = json_decode((string) $res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if ($data['access_token'] ?? null) {
            return $data['access_token'];
        }

        return null;
    }

    /**
     * openBrowser
     *
     * @param  string  $url
     * @param  IO      $io
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function openBrowser(string $url, IO $io): void
    {
        // Do we connect by SSH?
        if (!PlatformHelper::isWindows()) {
            $output = $this->app->runProcess('echo $SSH_CONNECTION')->getOutput();

            if (trim($output)) {
                $io->out("Open <info>{$url}</info> from your local browser.");

                return;
            }
        }

        $io->ask("Press [ENTER] to open browser.");

        $cmd = $this->environment->getPlatform()->isWindows()
            ? 'explorer'
            : 'open';

        exec(
            sprintf(
                '%s "%s"',
                $cmd,
                $url
            )
        );
    }

    /**
     * login
     *
     * @param  string  $token
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function auth(string $token): void
    {
        $this->client->authenticate($token, null, AuthMethod::CLIENT_ID);
    }

    /**
     * registerSshKey
     *
     * @param  string  $title
     * @param  string  $key
     *
     * @return  array
     *
     * @throws \Github\Exception\MissingArgumentException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function registerSshKey(string $title, string $key): array
    {
        return $this->client->currentUser()->keys()->create(
            [
                'title' => $title,
                'key' => $key,
            ]
        );
    }

    /**
     * getClient
     *
     * @return  Client
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        return $this->client = Client::createWithHttpClient(new \GuzzleHttp\Client());
    }

    /**
     * prepareRepo
     *
     * @param  bool  $sync
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function prepareRepo(bool $sync = true): bool
    {
        $repoLocalPath = DevtoolsHelper::getLocalPath();

        if (!is_dir($repoLocalPath . '/.git')) {
            $this->app->createProcess(
                'git clone git@github.com:' . DevtoolsHelper::REPO . '.git ' . DevtoolsHelper::TMP_FOLDER
            )
                ->setWorkingDirectory(LYRA_TMP)
                ->run($this->app->getProcessOutputCallback());
        }

        if ($sync) {
            $this->app->createProcess('git checkout master')
                ->setWorkingDirectory($repoLocalPath)
                ->run($this->app->getProcessOutputCallback());

            $this->app->createProcess('git pull origin master')
                ->setWorkingDirectory($repoLocalPath)
                ->run($this->app->getProcessOutputCallback());
        }

        return true;
    }

    /**
     * pushRepo
     *
     * @return  mixed
     *
     * @throws \Exception
     * @since  __DEPLOY_VERSION__
     */
    public function pushRepo(): void
    {
        $localPath = DevtoolsHelper::getLocalPath();
        $date = new \DateTime();

        $this->runProcessAt('git add --all', $localPath);
        $this->runProcessAt(
            'git commit -am "Update by lyra-cli on: ' . $date->format('Y-m-d H:i:s') . '"',
            $localPath
        );
        $this->runProcessAt('git push', $localPath);
    }

    protected function runProcessAt(string $cmd, string $cwd): int
    {
        return $this->app->createProcess($cmd)
            ->setWorkingDirectory($cwd)
            ->run($this->app->getProcessOutputCallback());
    }
}
