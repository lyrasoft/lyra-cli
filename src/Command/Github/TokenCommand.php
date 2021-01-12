<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2021 .
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Github;

use Lyrasoft\Cli\Service\GithubService;
use Windwalker\Console\Command\Command;
use Windwalker\DI\Annotation\Inject;
use Windwalker\Filesystem\File;

/**
 * The TokenCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class TokenCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'token';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Get Github token.';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     */
    protected $usage = '%s <cmd><command></cmd> <option>[option]</option>';

    /**
     * The manual about this command.
     *
     * @var  string
     */
    protected $help;

    /**
     * Property githubService.
     *
     * @Inject()
     *
     * @var GithubService
     */
    protected $githubService;

    /**
     * Initialise command.
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->addOption('refresh')
            ->alias('r')
            ->defaultValue(0)
            ->description('Refresh a new token.');

        $this->addOption('clear')
            ->alias('c')
            ->defaultValue(0)
            ->description('Clear token.');
    }

    /**
     * Execute this command.
     *
     * @return int|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function doExecute()
    {
        if ($this->getOption('clear')) {
            File::delete($this->githubService::tokenFile()->getPathname());

            $this->out('Clear Github token.');
            return true;
        }

        $token = $this->githubService->getStoredToken();

        if (!$token || $this->getOption('r')) {
            $token = $this->githubService->generateToken($this->getIO());
        }

        $this->out("<info>$token</info>");

        return true;
    }
}
