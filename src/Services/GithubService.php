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
use Windwalker\Console\IO;
use Windwalker\Environment\Environment;
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
    public function __construct(protected Environment $environment)
    {
        $this->client = $this->getClient();
    }

    /**
     * tokenFile
     *
     * @return  FileObject
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function tokenFile(): FileObject
    {
        return new FileObject(LYRA_TMP . '/github-token');
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
        $tokenFile = static::tokenFile();

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
        $tokenFile = static::tokenFile();

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

        $data = json_decode((string) $res->getBody(), true);

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
        // Do we conneted by SSH?
        $output = exec('echo $SSH_CONNECTION');

        if (trim($output)) {
            $io->out("Open <info>{$url}</info> from your local browser.");

            return;
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
}
