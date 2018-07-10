<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Command\Pstorm\PullConfigCommand;
use Lyrasoft\Cli\Command\Pstorm\PushConfigCommand;
use Lyrasoft\Cli\Command\Pstorm\SnifferCommand;
use Lyrasoft\Cli\Ioc;
use Windwalker\Console\Command\Command;

/**
 * The PstormCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PstormCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'pstorm';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'PhpStorm Helpers';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $usage = '%s <cmd><command></cmd> <option>[option]</option>';

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
        $this->addCommand(Ioc::make(PushConfigCommand::class));
        $this->addCommand(Ioc::make(PullConfigCommand::class));
        $this->addCommand(Ioc::make(SnifferCommand::class));
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     */
    protected function doExecute()
    {
        return parent::doExecute();
    }
}
