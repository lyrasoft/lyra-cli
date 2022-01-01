<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Services;

use Symfony\Component\Console\Question\Question;
use Windwalker\Console\IOInterface;
use Windwalker\Http\Output\OutputInterface;

/**
 * The EnvService class.
 */
class EnvService
{
    public function getUserDir(): string
    {
        return $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];
    }

    public function getComputerName(): ?string
    {
        if (isset($_SERVER['COMPUTERNAME'])) {
            return $_SERVER['COMPUTERNAME'];
        }

        if (gethostname()) {
            return gethostname();
        }

        return null;
    }

    public function getComputerNameOrAsk(IOInterface $io, Question|string $question): string
    {
        $name = $this->getComputerName();

        if ($name) {
            return $name;
        }

        return $io->ask($question);
    }
}
