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
 * The SshService class.
 */
class SshService
{
    public function __construct(protected EnvService $envService)
    {
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
        return file_get_contents($this->getRsaPubFile());
    }

    /**
     * getRsaFile
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRsaFile(): string
    {
        return $this->envService->getUserDir() . '/.ssh/id_rsa';
    }

    /**
     * getRasPubFile
     *
     * @param string|null $rsaFile
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getRsaPubFile(?string $rsaFile = null): string
    {
        $rsaFile = $rsaFile ?: $this->getRsaFile();

        return $rsaFile . '.pub';
    }
}
