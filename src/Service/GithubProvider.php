<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Service;

use Github\Client;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The GithubProvider class.
 *
 * @since  __DEPLOY_VERSION__
 */
class GithubProvider implements ServiceProviderInterface
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
        $container->prepareSharedObject(Client::class)
            ->alias('github', Client::class);
    }
}
