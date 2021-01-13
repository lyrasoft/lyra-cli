<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Github;

use Github\Exception\RuntimeException;
use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Lyrasoft\Cli\Service\GithubService;
use Lyrasoft\Cli\Service\SshService;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Prompter\BooleanPrompter;
use Windwalker\Console\Prompter\PasswordPrompter;
use Windwalker\Console\Prompter\Prompter;
use Windwalker\DI\Annotation\Inject;
use Windwalker\Filesystem\File;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AddSshCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'add-ssh';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Add ssh to your Github account.';

    /**
     * The manual about this command.
     *
     * @var  string
     *
     * @since  2.0
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
     * Property sshService.
     *
     * @Inject()
     *
     * @var SshService
     */
    protected $sshService;

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {
        $this->addOption('r')
            ->alias('refresh')
            ->description('Refresh SSH key.');
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @throws \Exception
     * @since  2.0
     */
    protected function doExecute()
    {
        $title = (string) $this->getArgument(0);

        if ($title === '') {
            if (isset($_SERVER['COMPUTERNAME'])) {
                $title = $_SERVER['COMPUTERNAME'];
            } elseif (gethostname()) {
                $title = gethostname();
            } else {
                $title = Prompter::notNullText('Key Title: ');
            }
        }

        $this->console->executeByPath('util ssh-key', ['m' => 1, 'r' => $this->getOption('r')], $this->io);

        $this->out()->out('Starting to add SSH key to GitHub...');

        $token = $this->githubService->getStoredToken();

        if (!$token) {
            $token = $this->githubService->deviceAuth($this->getIO());
        }

        $this->githubService->auth($token);

        $this->githubService->registerSshKey(
            $title,
            $this->sshService->getPubKey()
        );

        $this->out()->out(sprintf('Added SSH Key: <info>%s</info> to your GitHub account.', $title));

        return true;
    }
}
