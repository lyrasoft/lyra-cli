<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    MIT
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Process;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Interface ProcessRunnerInterface
 */
interface ProcessRunnerInterface
{
    /**
     * createProcess
     *
     * @param  string|array  $script
     *
     * @return  Process
     *
     * @since  3.5.22
     */
    public function createProcess(string|array $script): Process;

    /**
     * runProcess
     *
     * @param  string|array                   $script
     * @param  string|null                    $input
     * @param  bool|callable|OutputInterface  $output
     *
     * @return Process
     *
     * @since  3.5.5
     */
    public function runProcess(
        string|array $script,
        ?string $input = null,
        bool|callable|OutputInterface $output = true
    ): Process;
}
