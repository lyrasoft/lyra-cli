<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command\Pstorm;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait PstormTrait
 */
trait PstormTrait
{
    public function registerArgs(Command $command): void
    {
        $command->addOption(
            'file-templates',
            'f',
            InputOption::VALUE_NONE,
            'File Templates',
        );

        $command->addOption(
            'live-templates',
            'l',
            InputOption::VALUE_NONE,
            'Live Templates',
        );

        $command->addOption(
            'code-style',
            'c',
            InputOption::VALUE_NONE,
            'Code Style',
        );

        $command->addOption(
            'all',
            'a',
            InputOption::VALUE_NONE,
            'All types',
        );

        $command->addOption(
            'global',
            'g',
            InputOption::VALUE_NONE,
            'Use global phpstorm config',
        );
    }
}
