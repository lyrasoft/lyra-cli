<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Pstorm;

use Lyrasoft\Cli\Github\DevtoolsHelper;
use Lyrasoft\Cli\Github\GithubHelper;
use Lyrasoft\Cli\PhpStorm\PhpStormHelper;
use Windwalker\Console\Command\Command;
use Windwalker\Filesystem\File;
use Windwalker\Filesystem\Folder;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SnifferCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'sniffer';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Install and enable PHP Sniffer for this project.';

    /**
     * The manual about this command.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $help;

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {

    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     * @throws \LogicException
     * @throws \RuntimeException
     */
    protected function doExecute()
    {


        return true;
    }
}
