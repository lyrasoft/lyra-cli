<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Github;

/**
 * The DevtoolsHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DevtoolsHelper
{
    const REPO = 'lyrasoft/development-tools';
    const TMP_FOLDER = 'development-tools';

    /**
     * getUsername
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getUsername(): string
    {
        return explode('/', static::REPO)[0];
    }

    /**
     * getRepo
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getRepo(): string
    {
        return explode('/', static::REPO)[1];
    }

    /**
     * getLocalPath
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getLocalPath(): string
    {
        return LYRA_TMP . '/' . static::TMP_FOLDER;
    }
}
