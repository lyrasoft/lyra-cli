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
     * Property env.
     *
     * @var  Environment
     */
    protected $env;

    /**
     * PushConfigCommand constructor.
     *
     * @param string|null          $name
     * @param IOInterface|null     $io
     * @param AbstractCommand|null $parent
     * @param Environment          $env
     */
    public function __construct(string $name = null, IOInterface $io = null, AbstractCommand $parent = null, Environment $env)
    {
        $this->env = $env;

        parent::__construct($name, $io, $parent);
    }


    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {
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

        $this->out('You will push these configs:');

        foreach ($configs as $configName => $enabled) {
            $this->out(sprintf('    - <info>%s</info>', $configName));
        }

        $this->out(); // New line

        $configFolder = null;

        // Mac
        if ($this->env->getPlatform()->isUnix()) {
            $folders = glob($_SERVER['HOME'] . '/Library/Preferences/PhpStorm*');

            $configFolder = array_pop($folders);
            
            if (!$configFolder) {
                throw new \RuntimeException('Phpstorm config folder not found in: ' . $_SERVER['HOME'] . '/Library/Preferences');
            }
        } elseif ($this->env->getPlatform()->isWin()) {
            // Windows
            throw new \LogicException('Must implement Windows');
        }
        
        GithubHelper::prepareRepo();

        foreach ($configs as $configName => $enabled) {
            if (!$enabled) {
                continue;
            }

            $this->out()->out(sprintf('## Start Push: <comment>%s</comment>', $configName))->out();

            $files = Folder::files($configFolder . '/' . $configName, true, Folder::PATH_RELATIVE);

            foreach ($files as $file) {
                $srcFile = $configFolder . '/' . $configName . '/' . $file;

                $destFile = LYRA_TMP . '/' . DevtoolsHelper::TMP_FOLDER . '/Editor/PHPStorm/' . $configName . '/' . $file;

                $this->out(sprintf('[Updated] <info>%s</info>', $srcFile));

                File::write($destFile, file_get_contents($srcFile));
            }
        }

        GithubHelper::pushRepo();

        $this->out('Push completed');

        return true;
    }
}
