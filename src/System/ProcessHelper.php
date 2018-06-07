<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\System;

/**
 * The ProcessHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ProcessHelper
{
    /**
     * runAt
     *
     * @param string   $chdir
     * @param callable $handler
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function runAt(string $chdir, callable $handler)
    {
        $current = getcwd();
        chdir($chdir);

        $return = $handler();

        chdir($current);

        return $return;
    }
}
