<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command;

use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Services\SshService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IO;
use Windwalker\Console\IOInterface;
use Windwalker\Environment\PlatformHelper;
use Windwalker\Filesystem\FileObject;

/**
 * The SshKeyCommand class.
 */
#[CommandWrapper(description: 'Create or show ssh key for Github.')]
class SshKeyCommand implements CommandInterface
{
    public function __construct(protected SshService $sshService, protected Application $app)
    {
    }

    public function configure(Command $command): void
    {
        $command->addArgument(
            'rsa_file',
            InputArgument::OPTIONAL,
            'The private key file.'
        );

        $command->addOption(
            'refresh',
            'r',
            InputOption::VALUE_NONE,
            'Refresh SSH key.'
        );

        $command->addOption(
            'mute',
            'm',
            InputOption::VALUE_NONE,
            'Do not show result key.'
        );

        $command->addOption(
            'comment',
            '',
            InputOption::VALUE_REQUIRED,
            'Key comment.'
        );

        $command->addOption(
            'copy',
            'c',
            InputOption::VALUE_NONE,
            'Copy to clipboard.'
        );
    }

    public function execute(IOInterface $io): int
    {
        $rsaFile = $io->getArgument('rsa_file') ?: $this->sshService->getRsaFile();
        $rsaPubFile = $this->sshService->getRsaPubFile($rsaFile);

        $rsaFile = FileObject::wrap($rsaFile);
        $rsaPubFile = FileObject::wrap($rsaPubFile);

        if (!$rsaPubFile->isFile() || $io->getOption('refresh')) {
            if ($rsaPubFile->isFile()) {
                $qn = new ConfirmationQuestion(
                    'SSH key has exists, do you really want to re-generate? [N/y]',
                    false
                );

                $y = $io->ask($qn);

                if (!$y) {
                    return 0;
                }
            }

            $rsaFile->deleteIfExists();
            $rsaPubFile->deleteIfExists();

            $this->app->runProcess(
                sprintf('ssh-keygen -t rsa -b 4096 -f "%s" -P "" -C "%s"', $rsaFile, $io->getOption('comment')),
                "\n\n\n\n",
                true,
            );

            $io->out('Public key generated to: ' . $rsaPubFile);
        }

        if ($io->getOption('copy')) {
            $this->copy($rsaPubFile, $io);
        } elseif (!$io->getOption('mute')) {
            $this->showPublicKey($rsaPubFile, $io);
        }

        return 0;
    }

    /**
     * showPublicKey
     *
     * @param  FileObject   $rsaPubFile
     * @param  IOInterface  $io
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function showPublicKey(FileObject $rsaPubFile, IOInterface $io)
    {
        $io->newLine();
        $io->out('PUBLIC KEY START')
            ->out('----------------------------------');

        $io->out((string) $rsaPubFile->read(), false);

        $io->out('----------------------------------')
            ->out('PUBLIC KEY END');
    }

    protected function copy(FileObject $rsaPubFile, IO $io): void
    {
        if (PlatformHelper::isWindows()) {
            $this->app->runProcess("echo {$rsaPubFile->getPathname()} | clip");
        } elseif (PlatformHelper::isUnix()) {
            $this->app->runProcess("cat {$rsaPubFile->getPathname()} | pbcopy");
        } else {
            throw new \RuntimeException('Copy option currently not support this OS.');
        }

        $io->out('Copy SSH key to clipboard.');
    }
}
