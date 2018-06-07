<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\PhpStorm;

use Lyrasoft\Cli\Ioc;
use Windwalker\Environment\Environment;

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
        $env          = Ioc::getContainer()->get(Environment::class);
        $configFolder = '';

        // Mac
        if ($env->getPlatform()->isUnix()) {
            $folders = glob($_SERVER['HOME'] . '/Library/Preferences/PhpStorm*');

            $configFolder = array_pop($folders);

            if (!$configFolder) {
                throw new \RuntimeException('Phpstorm config folder not found in: ' . $_SERVER['HOME'] . '/Library/Preferences');
            }
        } elseif ($env->getPlatform()->isWin()) {
            // Windows
            throw new \LogicException('Must implement Windows');
        }

        if (!$configFolder) {
            throw new \RuntimeException('Phpstorm config folder not found.');
        }

        return $configFolder;
    }
}
