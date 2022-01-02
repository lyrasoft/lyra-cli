<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IOInterface;

/**
 * The PrCommand class.
 */
#[CommandWrapper(description: 'Get PR from particular repo.')]
class PrCommand implements CommandInterface
{
    public function __construct(protected Application $app)
    {
    }

    public function configure(Command $command): void
    {
        $command->addArgument(
            'pr',
            InputArgument::REQUIRED,
            'The PR number'
        );

        $command->addArgument(
            'branch',
            InputArgument::OPTIONAL,
            'The target branch name'
        );

        $command->addOption(
            'remote',
            'r',
            InputOption::VALUE_REQUIRED,
            'Remote name',
            'lyra'
        );

        $command->addOption(
            'checkout',
            'c',
            InputOption::VALUE_NONE,
            'Checkout to this branch instantly.'
        );
    }

    public function execute(IOInterface $io): int
    {
        $pr = $io->getArgument('pr');
        $remote = $io->getOption('remote');

        if (!$remote) {
            throw new \RuntimeException('Please provide Remote name.');
        }

        $branch = $io->getArgument('branch') ?: 'pr-' . $pr;
        $checkout = $io->getOption('checkout');

        $io->writeln(
            sprintf(
                'Fetch PR: <info>%s</info> from remote: <comment>%s</comment> as branch: <info>%s</info>',
                $pr,
                $remote,
                $branch
            )
        );

        if (!$checkout) {
            $io->newLine();
            $io->writeln('NOTE: You can use <info>-c</info> to auto checkout');
        }

        $io->newLine();

        $this->app->runProcess('git status');

        $this->app->runProcess(
            sprintf(
                'git fetch %s refs/pull/%s/head:%s',
                $remote,
                $pr,
                $branch
            )
        );

        if ($checkout) {
            $this->app->runProcess(
                sprintf(
                    'git checkout %s',
                    $branch
                )
            );
        }

        return 0;
    }
}
