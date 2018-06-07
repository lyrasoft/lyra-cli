<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Github;

use Github\Client;
use Lyrasoft\Cli\Ioc;
use Lyrasoft\Cli\System\ProcessHelper;
use Windwalker\Console\Prompter\Prompter;

/**
 * The GithubHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GithubHelper
{
    /**
     * prepareRepo
     *
     * @param bool $sync
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function prepareRepo($sync = true): bool
    {
        $repoLocalPath = LYRA_TMP . '/' . DevtoolsHelper::TMP_FOLDER;

        if (!is_dir($repoLocalPath . '/.git')) {
            ProcessHelper::runAt(LYRA_TMP, function () {
                system('git clone git@github.com:' . DevtoolsHelper::REPO . '.git ' . DevtoolsHelper::TMP_FOLDER);
            });
        }

        if ($sync) {
            ProcessHelper::runAt($repoLocalPath, function () {
                system('git checkout master');
                system('git pull origin master');
            });
        }

        return true;
    }

    /**
     * pushRepo
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function pushRepo()
    {
        $date = new \DateTime();

        return ProcessHelper::runAt(LYRA_TMP . '/' . DevtoolsHelper::TMP_FOLDER, function () use ($date) {
            system('git add --all');
            system('git commit -am "Update by lyra-cli on: ' . $date->format('Y-m-d H:i:s') . '"');
            system('git push');
        });
    }

    /**
     * auth
     *
     * @return Client
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function auth(): Client
    {
        $username = Prompter::text('Username: ');
        $password = Prompter::password('Password: ');

        $client = static::getClient();
        $client->authenticate($username, $password, Client::AUTH_HTTP_PASSWORD);

        return $client;
    }

    /**
     * getGithub
     *
     * @return  Client
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getClient(): Client
    {
        return Ioc::getContainer()->get('github');
    }

    /**
     * updateFile
     *
     * @param string $username
     * @param string $repo
     * @param string $branch
     * @param string $path
     * @param string $content
     * @param string $commitMessage
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function updateFile(string $username, string $repo, string $branch, string $path, string $content, string $commitMessage)
    {
        $client = static::getClient();

        $oldFile = $client->api('repo')->contents()->show($username, $repo, $path, $branch);

        return $client->api('repo')->contents()->update($username, $repo, $path, $content, $commitMessage, $oldFile['sha'], $branch);
    }
}
