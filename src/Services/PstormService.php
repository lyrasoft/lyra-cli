<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Services;

use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Lyrasoft\Cli\Ioc;
use Windwalker\Environment\Environment;
use Windwalker\Filesystem\Filesystem;

/**
 * The PstormService class.
 */
class PstormService
{
    public function __construct(protected EnvService $envService)
    {
    }

    public function getConfigFolder(): string
    {
        $home = $this->envService->getUserDir();

        // @see https://www.jetbrains.com/help/phpstorm/tuning-the-ide.html#config-directory
        if ($this->envService->isMac()) {
            $folderPatterns = [
                $home . '/Library/Preferences/PhpStorm*',
                $home . '/Library/Application Support/JetBrains/PhpStorm*',
            ];
        } elseif ($this->envService->isWindows()) {
            $folderPatterns = [
                $home . '/.PhpStorm*/config',
                $home . '/AppData/Roaming/JetBrains/PhpStorm*/jba_config',
            ];
        } else {
            throw new \RuntimeException('Only support Mac and Windows now.');
        }

        $folders = Filesystem::globAll($folderPatterns)->toArray();

        $configFolder = array_pop($folders);

        if ($configFolder === null) {
            throw new \RuntimeException('Phpstorm config folder not found.');
        }

        return $configFolder;
    }
}
