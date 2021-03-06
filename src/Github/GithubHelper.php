<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Github;

use Lyrasoft\Cli\Process\ProcessHelper;

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
        $repoLocalPath = DevtoolsHelper::getLocalPath();

        if (!is_dir($repoLocalPath . '/.git')) {
            ProcessHelper::runAt(LYRA_TMP, function () {
                system('git clone https://github.com/' . DevtoolsHelper::REPO . '.git ' . DevtoolsHelper::TMP_FOLDER);
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
     * @throws \Exception
     * @since  __DEPLOY_VERSION__
     */
    public static function pushRepo()
    {
        $date = new \DateTime();

        return ProcessHelper::runAt(DevtoolsHelper::getLocalPath(), function () use ($date) {
            system('git add --all');
            system('git commit -am "Update by lyra-cli on: ' . $date->format('Y-m-d H:i:s') . '"');
            system('git push');
        });
    }
}
