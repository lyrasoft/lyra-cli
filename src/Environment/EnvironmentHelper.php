<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Environment;

/**
 * The EnvironmentHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class EnvironmentHelper
{
    /**
     * getUserDir
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getUserDir()
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];

        return $home;
    }
}
