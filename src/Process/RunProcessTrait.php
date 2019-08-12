<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Process;

use Symfony\Component\Process\Process;

/**
 * The RunProcessTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait RunProcessTrait
{
    /**
     * @param string|array   $commandline The command line to run
     * @param string|null    $cwd         The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env         The environment variables or null to use the same environment as the current PHP process
     * @param mixed|null     $input       The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     * @param array          $options     An array of options for proc_open
     *
     * @return int
     * @throws \RuntimeException When proc_open is not installed
     */
    public function runProcess(
        string $commandline,
        string $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = null
    ) {
        $commandline = implode(' ', (array) $commandline);

        if (class_exists($this, 'out')) {
            $this->out('>> ' . $commandline);
        }

        return (new Process($commandline, $cwd, $env, null, $timeout, $options))
            ->setInput($input)
            ->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->err($buffer, false);
                } else {
                    $this->out($buffer, false);
                }
            });
    }
}
