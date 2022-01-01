<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command\Github;

use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Services\EnvService;
use Lyrasoft\Cli\Services\GithubService;
use Lyrasoft\Cli\Services\SshService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\Input\InputArgument;
use Windwalker\Console\IOInterface;

/**
 * The AddSshCommand class.
 */
#[CommandWrapper(description: 'Add ssh to your Github account.')]
class AddSshCommand implements CommandInterface
{
    public function __construct(
        protected EnvService $envService,
        protected GithubService $githubService,
        protected SshService $sshService,
        protected Application $app
    ) {
    }

    public function configure(Command $command): void
    {
        $command->addArgument(
            'title',
            InputArgument::OPTIONAL,
            'Key title'
        );

        $command->addOption(
            'refresh',
            'r',
            InputOption::VALUE_NONE,
            'Refresh a new token.',
        );

        $command->addOption(
            'identify',
            'i',
            InputOption::VALUE_REQUIRED,
            'The public key file.',
        );
    }

    public function execute(IOInterface $io): int
    {
        $title = $io->getArgument('title');

        $title = $title ?: $this->envService->getComputerNameOrAsk($io, 'Key title: ');

        $this->app->runCommand(
            'ssh-key',
            [
                '--mute' => true,
                '-r' => (bool) $io->getOption('refresh')
            ]
        );

        $io->writeln('Starting to add SSH key to GitHub...');

        $token = $this->githubService->getStoredToken();

        if (!$token) {
            $token = $this->githubService->deviceAuth($io);
        }

        $this->githubService->auth($token);

        $i = $io->getOption('identify');

        if ($i) {
            $key = file_get_contents($i);
        } else {
            $key = $this->sshService->getPubKey();
        }

        $this->githubService->registerSshKey($title, $key);

        $io->newLine();
        $io->writeln(sprintf('Added SSH Key: <info>%s</info> to your GitHub account.', $title));

        return 0;
    }
}
