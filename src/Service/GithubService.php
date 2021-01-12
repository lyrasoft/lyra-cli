<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Service;

use Github\Client;
use Windwalker\Console\IO\IO;
use Windwalker\DI\Annotation\Inject;
use Windwalker\Environment\Environment;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpClient;

/**
 * The GithubService class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GithubService
{
    protected const CLIENT_ID = '9935f1864afade172e5d';

    /**
     * Property client.
     *
     * @var Client
     */
    protected $client;

    /**
     * @Inject
     *
     * @var Environment
     */
    protected $environment;

    /**
     * GithubService constructor.
     */
    public function __construct()
    {
        $this->client = $this->getClient();
    }

    /**
     * deviceAuth
     *
     * @return  string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
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
                        'scope' => 'repo'
                    ]
                ),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $data = json_decode((string) $res->getBody(), true);

        $io->out("Please enter this code to your browser: <info>{$data['user_code']}</info>");

        $this->openBrowser($data['verification_uri']);

        sleep(5);

        $total = 0;

        while (!$token = $this->accessToken($data['device_code'])) {
            if ($total > 150) {
                throw new \RuntimeException('Please enter code in 150 seconds.');
                break;
            }

            $total += 6;
            sleep(6);
        }

        return $token;
    }

    /**
     * checkCode
     *
     * @param string $deviceCode
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
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code'
                    ]
                ),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
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
     * @param string $url
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function openBrowser(string $url): void
    {
        $cmd = $this->environment->getPlatform()->isWin()
            ? 'start'
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
     * @param string $username
     * @param string $password
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function login(string $username, string $password): void
    {
        $this->auth($username, $password);

        $this->client->users()->show($username);
    }

    /**
     * login
     *
     * @param string $username
     * @param string $password
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function auth(string $token): void
    {
        $this->client->authenticate($token, null, Client::AUTH_CLIENT_ID);
    }

    /**
     * registerSshKey
     *
     * @param string $title
     * @param string $key
     *
     * @return  array
     *
     * @throws \Github\Exception\MissingArgumentException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function registerSshKey(string $title, string $key): array
    {
        return $this->client->currentUser()->keys()->create([
            'title' => $title,
            'key' => $key
        ]);
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
