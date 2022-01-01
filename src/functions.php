<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Get ENV var.
     *
     * @param  string      $name
     * @param  mixed|null  $default
     *
     * @return string|null
     *
     * @since  3.3
     */
    function env(string $name, mixed $default = null): ?string
    {
        return $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    }
}
