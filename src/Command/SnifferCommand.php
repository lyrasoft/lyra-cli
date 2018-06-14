<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Command\Sniffer\SyncCommand;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Windwalker\Console\Command\Command;

/**
 * The PstormCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SnifferCommand extends Command
{
    use RunProcessTrait;

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
    protected $description = 'PHP Sniffer actions.';

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
        $this->addCommand(SyncCommand::class);
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
