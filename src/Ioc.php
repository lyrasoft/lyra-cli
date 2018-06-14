<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli;

use Windwalker\DI\Container;

/**
 * The Ioc class.
 *
 * @method static get($key, $forceNew = false)
 * @method static createObject(string $class, array $args = [], $shared = false, $protected = false)
 * @method static createSharedObject(string $class, array $args = [], $protected = false)
 * @method static newInstance($class, array $args = [])
 *
 * @since  __DEPLOY_VERSION__
 */
class Ioc
{
    /**
     * Property container.
     *
     * @var Container
     */
    protected static $container;

    /**
     * getContainer
     *
     * @return  Container
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getContainer()
    {
        if (!static::$container) {
            static::$container = new Container();
        }

        return static::$container;
    }

    /**
     * __callStatic
     *
     * @param string $name
     * @param array  $args
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function __callStatic($name, $args)
    {
        $container = static::getContainer();

        return $container->$name(...$args);
    }
}
