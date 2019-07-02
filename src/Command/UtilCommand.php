<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Command\Util\SshKeyCommand;
use Lyrasoft\Cli\Ioc;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Windwalker\Console\Command\Command;

/**
 * The GithubCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class UtilCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'util';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Some utilities tools.';

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
        $this->addCommand(Ioc::make(SshKeyCommand::class));
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
