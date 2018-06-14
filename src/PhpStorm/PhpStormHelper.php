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
        $env = Ioc::getContainer()->get(Environment::class);

        if ($env->getPlatform()->isUnix()) {
            $folderPattern = $_SERVER['HOME'] . '/Library/Preferences/PhpStorm*';
        } elseif ($env->getPlatform()->isWin()) {
            $folderPattern = $_SERVER['HOME'] . '/.PhpStorm*';
        } else {
            throw new \RuntimeException('Only support Mac and Windows now.');
        }

        $folders = glob($folderPattern);

        $configFolder = array_pop($folders);

        if ($configFolder === null) {
            throw new \RuntimeException('Phpstorm config folder not found in: ' . \dirname($folderPattern));
        }

        return $configFolder;
    }
}