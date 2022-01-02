<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command\Pstorm;

use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Helper\DevtoolsHelper;
use Lyrasoft\Cli\Services\EnvService;
use Lyrasoft\Cli\Services\GithubService;
use Lyrasoft\Cli\Services\PstormService;
use Symfony\Component\Console\Command\Command;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IOInterface;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;

/**
 * The PushConfigCommand class.
 */
#[CommandWrapper(description: 'Push config to repository.')]
class PushConfigCommand implements CommandInterface
{
    use PstormTrait;

    public function __construct(
        protected Application $app,
        protected EnvService $envService,
        protected PstormService $pstormService,
        protected GithubService $githubService
    ) {
    }

    public function configure(Command $command): void
    {
        $this->registerArgs($command);
    }

    public function execute(IOInterface $io): int
    {
        $configs = [
            'fileTemplates' => $io->getOption('all') ?: $io->getOption('file-templates'),
            'codestyles' => $io->getOption('all') ?: $io->getOption('code-style'),
            'templates' => $io->getOption('all') ?: $io->getOption('live-templates'),
        ];

        if (!\in_array(true, $configs, true)) {
            throw new \RuntimeException(
                'Please provide at least one config name or use -a|--all to handle all supported configs.'
            );
        }

        $io->writeln('You will push these configs:');

        foreach ($configs as $configName => $enabled) {
            $io->writeln(sprintf('    - <info>%s</info>', $configName));
        }

        $io->newLine(); // New line

        $configFolder = $this->pstormService->getConfigFolder();

        $this->githubService->prepareRepo();

        foreach ($configs as $configName => $enabled) {
            if (!$enabled) {
                continue;
            }

            $io->newLine();
            $io->writeln(sprintf('## Start Push: <comment>%s</comment>', $configName));
            $io->newLine();

            $files = Filesystem::files($configFolder . '/' . $configName, true);

            /** @var FileObject $file */
            foreach ($files as $file) {
                $srcFile = $configFolder . '/' . $configName . '/' . $file->getRelativePathname();

                $destFile = DevtoolsHelper::getLocalPath()
                    . '/Editor/PHPStorm/' . $configName . '/' . $file->getRelativePathname();

                $io->writeln(sprintf('[Updated] <info>%s</info>', $srcFile));

                Filesystem::write($destFile, file_get_contents($srcFile));
            }
        }

        $this->githubService->pushRepo();

        $io->writeln('Push completed');

        return 0;
    }
}
