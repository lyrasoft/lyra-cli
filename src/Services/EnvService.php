<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Services;

/**
 * The EnvService class.
 */
class EnvService
{
    public function getUserDir(): string
    {
        return $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];
    }
}
