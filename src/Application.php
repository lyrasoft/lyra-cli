<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli;

use Windwalker\Console\Console;
use Windwalker\Console\IO\IOInterface;
use Windwalker\DI\Container;
use Windwalker\Structure\Structure;

/**
 * The Application class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Application extends Console
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $title = 'LYRASOFT CLI';

    /**
     * Property version.
     *
     * @var  string
     */
    protected $version = null;

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'LYRASOFT internal tool to help us setup develop environment.';

    /**
     * Property container.
     *
     * @var  Container
     */
    protected $container;

    /**
     * Application constructor.
     *
     * @param IOInterface|null $io
     * @param Structure|null   $config
     * @param Container        $container
     */
    public function __construct(IOInterface $io, Structure $config, Container $container)
    {
        $this->version = trim(file_get_contents(__DIR__ . '/../VERSION'));

        parent::__construct($io, $config);

        $this->container = $container;
    }

    /**
     * Method to get property Container
     *
     * @return  Container
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContainer()
    {
        return $this->container;
    }
}
