<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Helper;

/**
 * The ComposerHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ComposerHelper
{
    /**
     * getGlobalPath
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getGlobalPath(): string
    {
        return (string) realpath(LYRA_ROOT . '/../../..');
    }
}
