<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Provider;

use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Services\EnvService;
use Lyrasoft\Cli\Services\GithubService;
use Lyrasoft\Cli\Services\PstormService;
use Lyrasoft\Cli\Services\SshService;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Windwalker\Attributes\AttributeType;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IO;
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
    public function __construct(protected Application $app)
    {
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->share(Container::class, $container);

        $container->prepareSharedObject(Platform::class);
        $container->prepareSharedObject(Environment::class);

        // Services
        $container->share(Application::class, $this->app);
        $container->prepareSharedObject(EnvService::class);
        $container->prepareSharedObject(SshService::class);
        $container->prepareSharedObject(GithubService::class);
        $container->prepareSharedObject(PstormService::class);

        // Attributes
        $attributeResolver = $container->getAttributesResolver();

        $attributeResolver->registerAttribute(CommandWrapper::class, AttributeType::CLASSES);
    }
}
