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
use Windwalker\String\Str;
use Windwalker\Structure\Format\IniFormat;
use Windwalker\Structure\Structure;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DeployKeyCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'deploy-key';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Add ssh to your Github account.';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $usage = '%s <cmd><command></cmd> <repository> <title> <option>[option]</option>';

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
        $repo = (string) $this->getArgument(0);
        $title = (string) $this->getArgument(1);

        if (!$repo) {
            // Github config
            $configPath = getcwd() . '/.git/config';

            $config = (new Structure())->loadString(
                str_replace(' = ', '=', file_get_contents($configPath)),
                'ini',
                ['processSections' => true]
            );

            $url = $config->get('remote "origin".url');
            
            preg_match('/git\@github\.com\:(.+)\.git/', $url, $matches);
            
            $repo = $matches[1] ?? '';
        }

        if (!$repo || !Str::contains($repo, '/')) {
            throw new \UnexpectedValueException('Unknown GitHub repository: ' . $repo);
        }

        [$account, $repo] = explode('/', $repo, 2);

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

        $this->out()->out('Starting to add Deploy key to GitHub...');

        $username = Prompter::notNullText(
            'Username: ',
            '',
            'Please enter username.',
            3,
            'No username.'
        );

        $passwordPrompter = (new PasswordPrompter(
            'Password: ',
            function ($pass) use ($username) {
                try {
                    $this->githubService->login($username, $pass);
                } catch (RuntimeException $e) {
                    if ($e->getCode() === 401) {
                        return false;
                    }

                    throw $e;
                }

                return true;
            }
        ))->setAttemptTimes(3)
            ->failToClose(true)
            ->setNoValidMessage('Password invalid');

        $passwordPrompter->ask();
        
        $this->githubService->getClient()->repository()->keys()
            ->create($account, $repo, ['title' => $title, 'key' => $this->sshService->getPubKey()]);

        return true;
    }
}
