<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Command\Github\AddSshCommand;
use Lyrasoft\Cli\Command\Github\DeployKeyCommand;
use Lyrasoft\Cli\Command\Github\TokenCommand;
use Lyrasoft\Cli\Ioc;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Windwalker\Console\Command\Command;

/**
 * The GithubCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GithubCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'github';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Github operation.';

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
        $this->addCommand(Ioc::make(TokenCommand::class));
        $this->addCommand(Ioc::make(AddSshCommand::class));
        $this->addCommand(Ioc::make(DeployKeyCommand::class));
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
