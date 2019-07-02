<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Provider;

use Lyrasoft\Cli\Application;
use Windwalker\Console\AbstractConsole;
use Windwalker\Console\Console;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Environment\Environment;
use Windwalker\Environment\Platform;

/**
 * The AppProvider class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AppProvider implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return  void
     */
    public function register(Container $container)
    {
        $container->share(Container::class, $container);
        $container->bindShared(IOInterface::class, IO::class);

        $container->prepareSharedObject(Application::class)
            ->alias(Console::class, Application::class)
            ->alias(AbstractConsole::class, Application::class);

        $container->prepareSharedObject(Platform::class);
        $container->prepareSharedObject(Environment::class);
    }
}
