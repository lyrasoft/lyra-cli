<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Service;

use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Filesystem\File;

/**
 * The SsshSErvice class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SshService
{
    /**
     * Property io.
     *
     * @var  IOInterface
     */
    protected $io;

    /**
     * SshService constructor.
     *
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * getPubKey
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getPubKey(): string
    {
        return file_get_contents($this->getRasPubFile());
    }

    /**
     * getRsaFile
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRsaFile():string
    {
        return EnvironmentHelper::getUserDir() . '/.ssh/id_rsa';
    }

    /**
     * getRasPubFile
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRasPubFile(): string
    {
        return $this->getRsaFile() . '.pub';
    }
}
