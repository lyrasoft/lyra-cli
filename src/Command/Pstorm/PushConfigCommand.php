<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Pstorm;

use Windwalker\Console\Command\Command;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PushConfigCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'push-config';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Push config to repository.';

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
        //
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        $configs = [
            'fileTemplate' => $this->getOptions('f'),
            'codestyles' => $this->getOption('c'),
            'templates' => $this->getOptions('l')
        ];

        if (!\in_array(true, $configs, true)) {
            throw new \RuntimeException('Please provide at least one config name or use -a|--all to handle all supported configs.');
        }
    }
}
