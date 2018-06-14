<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Sniffer;

use Lyrasoft\Cli\Github\GithubHelper;
use Windwalker\Console\Command\Command;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SyncCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'sync';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Sync LYRASOFT Sniffer settings.';

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
        //
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
        GithubHelper::prepareRepo(true);

        return true;
    }
}
