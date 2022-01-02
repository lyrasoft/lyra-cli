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
use Windwalker\Filesystem\Path;

/**
 * The PullConfigCommand class.
 */
#[CommandWrapper(description: 'Pull config to my phpstorm or project.')]
class PullConfigCommand implements CommandInterface
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

        $global = $io->getOption('global');

        $io->writeln(
            sprintf(
                'You will pull these configs to <comment>%s</comment>:',
                $global ? 'PhpStorm global config' : 'Current Project'
            )
        );

        foreach ($configs as $configName => $enabled) {
            $io->writeln(sprintf('    - <info>%s</info>', $configName));
        }

        $io->newLine();

        $configFolder = $global ? $this->pstormService->getConfigFolder() : getcwd() . '/.idea';

        $this->githubService->prepareRepo();

        $io->writeln(sprintf('Found config dir: <info>%s</info>', $configFolder));

        foreach ($configs as $configName => $enabled) {
            if (!$enabled) {
                continue;
            }

            $io->newLine();
            $io->writeln(sprintf('## Start Copy: <comment>%s</comment>', $configName));
            $io->newLine();

            $files = Filesystem::files(
                DevtoolsHelper::getLocalPath() . '/Editor/PHPStorm/' . $configName,
                true
            );

            /** @var FileObject $file */
            foreach ($files as $file) {
                $srcFile = DevtoolsHelper::getLocalPath()
                    . '/Editor/PHPStorm/' . $configName . '/' . $file->getRelativePathname();

                $destFile = $configFolder . '/' . $configName . '/' . $file->getRelativePathname();

                $io->writeln(sprintf('[Updated] <info>%s</info>', $destFile));

                Filesystem::write($destFile, file_get_contents($srcFile));
            }

            if ($configName === 'fileTemplates' && $this->envService->isWindows()) {
                $io->writeln(
                    sprintf(
                        'Move: %s to %s',
                        $configFolder . '/' . $configName,
                        Path::normalize($configFolder . '/..')
                    )
                );

                Filesystem::move(
                    $configFolder . '/' . $configName,
                    $configFolder . '/../' . $configName,
                    true
                );
            }
        }

        $io->writeln('Update config completed');

        return 0;
    }
}
