<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli;

use Windwalker\Console\Console;

/**
 * The Application class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Application extends Console
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $title = 'LYRASOFT CLI';

    /**
     * Property version.
     *
     * @var  string
     */
    protected $version = '1.0.0';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'LYRASOFT internal tool to help us setup develop environment.';
}
