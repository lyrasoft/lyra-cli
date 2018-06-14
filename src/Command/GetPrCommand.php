<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Command\Pstorm\PullConfigCommand;
use Lyrasoft\Cli\Command\Pstorm\PushConfigCommand;
use Lyrasoft\Cli\Ioc;
use Symfony\Component\Process\Process;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Exception\WrongArgumentException;

/**
 * The PstormCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GetPrCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'pr';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Get PR from particular repo.';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $usage = '%s <cmd><PR number></cmd> <option>[branch]</option> <option>[option]</option>';

    /**
     * The manual about this command.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $help;

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {
        $this->addOption('remote')
            ->alias('r')
            ->defaultValue('lyra')
            ->description('Remote name.');

        $this->addOption('checkout')
            ->alias('c')
            ->defaultValue(false)
            ->description('Checkout to this branch instantly.');
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     */
    protected function doExecute()
    {
        $remote = $this->getOption('r');
        $pr = (int) $this->getArgument(0);

        if (!$remote) {
            throw new WrongArgumentException('Please provide Remote name.');
        }

        if (!$pr) {
            throw new WrongArgumentException('Please provide PR number.');
        }

        $branch = $this->getArgument(1, 'pr-' . $pr);

        (new Process(sprintf(
            'git fetch %s refs/pull/%s/head:%s',
            $remote,
            $pr,
            $branch
        )))->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->err($buffer, false);
            } else {
                $this->out($buffer, false);
            }
        });

        if ($this->getOption('c')) {
            (new Process(sprintf(
                'git checkout %s',
                $branch
            )))->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->err($buffer, false);
                } else {
                    $this->out($buffer, false);
                }
            });
        }

        return true;
    }
}
