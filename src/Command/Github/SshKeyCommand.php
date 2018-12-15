<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Github;

use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Prompter\BooleanPrompter;
use Windwalker\Filesystem\File;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SshKeyCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'ssh-key';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Create or show ssh key for Github.';

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
        $this->addOption('r')
            ->alias('refresh')
            ->description('Refresh SSH key.');
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     * @throws \LogicException
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        $rsaFile = EnvironmentHelper::getUserDir() . '/.ssh/id_rsa';
        $rsaPubFile = $rsaFile . '.pub';

        if (!is_file($rsaPubFile) || $this->getOption('r')) {
            if (is_file($rsaPubFile)) {
                $y = (new BooleanPrompter())
                    ->ask('SSH key has exists, do you really want to re-generate? [N/y]', false);

                if (!$y) {
                    return false;
                }
            }

            File::delete([$rsaFile, $rsaPubFile]);

            $email = $this->in('Tell me your E-mail: ');

            $this->runProcess(
                sprintf('ssh-keygen -t rsa -b 4096 -C "%s"', $email),
                null,
                null,
                "\n\n\n\n"
            );
        }

        $this->showPublicKey($rsaPubFile);

        return true;
    }

    /**
     * showPublicKey
     *
     * @param string $rsaPubFile
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function showPublicKey(string $rsaPubFile)
    {
        $this->out()->out('PUBLIC KEY START')
            ->out('----------------------------------');

        $this->out(file_get_contents($rsaPubFile));

        $this->out('----------------------------------')
            ->out('PUBLIC KEY END');
    }
}
