<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Pstorm;

use Lyrasoft\Cli\Github\DevtoolsHelper;
use Lyrasoft\Cli\Github\GithubHelper;
use Lyrasoft\Cli\PhpStorm\PhpStormHelper;
use Windwalker\Console\Command\AbstractCommand;
use Windwalker\Console\Command\Command;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Environment\Environment;
use Windwalker\Filesystem\File;
use Windwalker\Filesystem\Folder;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PullConfigCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'pull-config';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Pull config to my phpstorm or project.';

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
        // Options
        $this->addOption('f')
            ->alias('file-template')
            ->description('File Template')
            ->defaultValue(0);

        $this->addOption('l')
            ->alias('live-template')
            ->description('Live Template')
            ->defaultValue(0);

        $this->addOption('c')
            ->alias('code-style')
            ->description('Code Style')
            ->defaultValue(0);

        $this->addOption('a')
            ->alias('all')
            ->description('All types')
            ->defaultValue(0);

        $this->addGlobalOption('g')
            ->alias('global')
            ->description('Use global phpstorm config')
            ->defaultValue(0);
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
        $configs = [
            'fileTemplates' => $this->getOption('a') ? true : $this->getOption('f'),
            'codestyles'    => $this->getOption('a') ? true : $this->getOption('c'),
            'templates'     => $this->getOption('a') ? true : $this->getOption('l')
        ];

        if (!\in_array(true, $configs, true)) {
            throw new \RuntimeException('Please provide at least one config name or use -a|--all to handle all supported configs.');
        }

        $global = $this->getOption('g');

        $this->out(sprintf('You will pull these configs to <comment>%s</comment>:', $global ? 'PhpStorm global config' : 'Current Project'));

        foreach ($configs as $configName => $enabled) {
            $this->out(sprintf('    - <info>%s</info>', $configName));
        }

        $this->out(); // New line

        $configFolder = $global ? PhpStormHelper::getConfigFolder() : getcwd() . '/.idea';
        
        GithubHelper::prepareRepo();

        foreach ($configs as $configName => $enabled) {
            if (!$enabled) {
                continue;
            }

            $this->out()->out(sprintf('## Start Copy: <comment>%s</comment>', $configName))->out();

            $files = Folder::files(LYRA_TMP . '/' . DevtoolsHelper::TMP_FOLDER . '/Editor/PHPStorm/' . $configName, true, Folder::PATH_RELATIVE);

            foreach ($files as $file) {
                $srcFile = LYRA_TMP . '/' . DevtoolsHelper::TMP_FOLDER . '/Editor/PHPStorm/' . $configName . '/' . $file;

                $destFile = $configFolder . '/' . $configName . '/' . $file;

                $this->out(sprintf('[Updated] <info>%s</info>', $destFile));

                File::write($destFile, file_get_contents($srcFile));
            }
        }

        $this->out('Update config completed');

        return true;
    }
}
