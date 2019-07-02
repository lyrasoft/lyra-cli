<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Debug;

use Windwalker\String\Mbstring;
use Windwalker\String\Str;
use Windwalker\String\Utf8String;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Reflection\ReflectionHelper;

/**
 * The BacktraceHelper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class BacktraceHelper
{
    /**
     * whoCallMe
     *
     * @param int  $backwards
     * @param bool $replaceRoot
     *
     * @return  array
     */
    public static function whoCallMe($backwards = 2, bool $replaceRoot = true)
    {
        return static::normalizeBacktrace(debug_backtrace()[$backwards], $replaceRoot);
    }

    /**
     * normalizeBacktrace
     *
     * @param array $trace
     * @param bool  $replaceRoot
     *
     * @return  array
     */
    public static function normalizeBacktrace(array $trace, bool $replaceRoot = true)
    {
        $trace = (array) Arr::def($trace, 'args', []);
        $trace = (array) Arr::def($trace, 'file', []);
        $trace = (array) Arr::def($trace, 'class', []);
        $trace = (array) Arr::def($trace, 'type', []);
        $trace = (array) Arr::def($trace, 'function', []);
        $trace = (array) Arr::def($trace, 'line', []);

        $args = [];

        foreach ($trace['args'] as $arg) {
            if (is_array($arg)) {
                $arg = 'Array';
            } elseif (is_object($arg)) {
                $arg = ReflectionHelper::getShortName($arg);
            } elseif (is_string($arg)) {
                if (Mbstring::strlen($arg) > 20) {
                    $arg = Utf8String::substr($arg, 0, 20) . '...';
                }

                $arg = Str::wrap($arg);
            } elseif ($arg === null) {
                $arg = 'NULL';
            } elseif (is_bool($arg)) {
                $arg = $arg ? 'TRUE' : 'FALSE';
            }

            $args[] = $arg;
        }

        $file = $trace['file'] ?? '';

        if ($file) {
            $file = $replaceRoot ? static::replaceRoot($file) : $file;
            $file .= ':' . $trace['line'];
        }

        return [
            'file' => $file,
            'function' => ($trace['class'] ? $trace['class'] . $trace['type'] : null) . $trace['function'] .
                sprintf('(%s)', implode(', ', $args)),
            'pathname' => $trace['file'],
            'line' => $trace['line']
        ];
    }

    /**
     * normalizeBacktraces
     *
     * @param array $traces
     * @param bool  $replaceRoot
     *
     * @return  array
     */
    public static function normalizeBacktraces(array $traces, bool $replaceRoot = true)
    {
        $return = [];

        foreach ($traces as $trace) {
            $return[] = $trace ? static::normalizeBacktrace($trace) : null;
        }

        return $return;
    }

    /**
     * traceAsString
     *
     * @param int   $i
     * @param array $trace
     * @param bool  $replaceRoot
     *
     * @return  string
     *
     * @since  3.5.7
     */
    public static function traceAsString(int $i, array $trace, bool $replaceRoot = true): string
    {
        $frameData = static::normalizeBacktrace($trace, $replaceRoot);

        return sprintf(
            '%3d. %s %s',
            $i,
            $frameData['function'],
            $frameData['file']
        );
    }

    /**
     * replaceRoot
     *
     * @param string $file
     *
     * @return  string
     */
    public static function replaceRoot($file)
    {
        if (defined('WINDWALKER_ROOT')) {
            $file = 'ROOT' . substr($file, strlen(WINDWALKER_ROOT));
        }

        return $file;
    }
}
