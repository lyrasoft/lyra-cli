<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Service;

use Github\Client;
use Windwalker\DI\Annotation\Inject;
use Windwalker\Http\HttpClient;

/**
 * The GithubService class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GithubService
{
    /**
     * Property client.
     *
     * @var Client
     */
    protected $client;

    /**
     * GithubService constructor.
     */
    public function __construct()
    {
        $this->client = $this->getClient();
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
    public function auth(string $username, string $password): void
    {
        $this->client->authenticate($username, $password, Client::AUTH_HTTP_PASSWORD);
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

        return $this->client = Client::createWithHttpClient(new HttpClient());
    }
}
