<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\PhpStorm;

use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Lyrasoft\Cli\Ioc;
use Windwalker\Environment\Environment;
use Windwalker\Filesystem\Filesystem;

/**
 * The PhpStormHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PhpStormHelper
{
    /**
     * getConfigFolder
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function getConfigFolder(): string
    {
        /** @var Environment $env */
        $env = Ioc::get(Environment::class);

        $home = EnvironmentHelper::getUserDir();

        // @see https://www.jetbrains.com/help/phpstorm/tuning-the-ide.html#config-directory
        if ($env->getPlatform()->isUnix()) {
            $folderPatterns = [
                '/Library/Preferences/PhpStorm*',
                '/Library/Application Support/JetBrains/PhpStorm*',
            ];
        } elseif ($env->getPlatform()->isWin()) {
            $folderPatterns = [
                '/.PhpStorm*/config',
                '/AppData/Roaming/JetBrains/PhpStorm*/jba_config',
            ];
        } else {
            throw new \RuntimeException('Only support Mac and Windows now.');
        }

        $folders = Filesystem::globAll($home, $folderPatterns);

        $configFolder = array_pop($folders);

        if ($configFolder === null) {
            throw new \RuntimeException('Phpstorm config folder not found.');
        }

        return $configFolder;
    }
}
