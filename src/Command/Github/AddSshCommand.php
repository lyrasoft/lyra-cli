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
        $this->out('Please provide Github credentials');
        $title = $_SERVER['COMPUTERNAME'] ?? Prompter::notNullText('Key Title: ');

        $this->console->executeByPath('util ssh-key', ['m' => 1, 'r' => $this->getOption('r')], $this->io);

        $this->out()->out('Starting to add SSH key to GitHub...');

        $username = Prompter::notNullText('Username: ', '', 'Please enter username.');
        $passwordPrompter = (new PasswordPrompter('Password: ', function ($pass) use($username, $title) {
            $this->githubService->login($username, $pass);

            try {
                $this->githubService->registerSshKey(
                    $title,
                    $this->sshService->getPubKey()
                );
            } catch (RuntimeException $e) {
                if ($e->getCode() === 401) {
                    return false;
                }

                throw $e;
            }

            return true;
        }))->setAttemptTimes(3)
            ->setNoValidMessage('Password invalid');

        $passwordPrompter->ask();

        $this->out()->out(sprintf('Added SSH Key: <info>%s</info> to your GitHub account.', $title));

        return true;
    }
}
