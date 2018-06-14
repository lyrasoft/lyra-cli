<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Composer;

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
     * @return  bool|string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getVendorPath()
    {
        return realpath(LYRA_ROOT . '/../../..');
    }
}
