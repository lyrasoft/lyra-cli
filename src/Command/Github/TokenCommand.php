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
use Lyrasoft\Cli\Services\GithubService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IOInterface;

use function Clue\StreamFilter\append;

/**
 * The TokenCommand class.
 */
#[CommandWrapper(description: 'Get a Github personal token.')]
class TokenCommand implements CommandInterface
{
    public function __construct(protected GithubService $githubService, protected Application $app)
    {
    }

    public function configure(Command $command): void
    {
        $command->addOption(
            'refresh',
            'r',
            InputOption::VALUE_NONE,
            'Refresh a new token.',
        );

        $command->addOption(
            'clear',
            '',
            InputOption::VALUE_NONE,
            'Clear token.',
        );

        $command->addOption(
            'copy',
            'c',
            InputOption::VALUE_NONE,
            'Copy to clipboard.'
        );
    }

    public function execute(IOInterface $io): int
    {
        if ($io->getOption('clear')) {
            $this->githubService->tokenFile()->deleteIfExists();

            $io->writeln('Clear Github token.');
            return 0;
        }

        $token = $this->githubService->getStoredToken();

        if (!$token || $io->getOption('refresh')) {
            $token = $this->githubService->generateToken($io);
        }

        if ($io->getOption('copy')) {
            $this->app->copyText($token);

            $io->writeln('Copy token text to clipboard.');
        } else {
            $io->writeln('TOKEN:');
            $io->writeln("<info>$token</info>");
        }

        return 0;
    }
}
