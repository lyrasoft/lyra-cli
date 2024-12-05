<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Lyrasoft\Cli\Command\Github;

use Lyrasoft\Cli\Application;
use Lyrasoft\Cli\Services\EnvService;
use Lyrasoft\Cli\Services\GithubService;
use Lyrasoft\Cli\Services\SshService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\Input\InputArgument;
use Windwalker\Console\IOInterface;
use Windwalker\Data\Collection;
use Windwalker\Environment\Environment;
use Windwalker\Environment\PlatformHelper;
use Windwalker\Filesystem\Filesystem;

/**
 * The DeployKeyCommand class.
 */
#[CommandWrapper(description: 'Add ssh to your Github account.')]
class DeployKeyCommand implements CommandInterface
{
    public function __construct(
        protected Application $app,
        protected GithubService $githubService,
        protected SshService $sshService,
        protected EnvService $envService,
    ) {
        //
    }

    public function configure(Command $command): void
    {
        $command->addArgument(
            'repo',
            InputArgument::OPTIONAL
        );

        $command->addArgument(
            'title',
            InputArgument::OPTIONAL
        );

        $command->addOption(
            'refresh',
            'r',
            InputOption::VALUE_NONE,
            'Refresh a new token.',
        );

        $command->addOption(
            'path',
            '',
            InputOption::VALUE_REQUIRED,
            'Key path.',
        );
    }

    public function execute(IOInterface $io): int
    {
        $repo    = (string) $io->getArgument('repo');
        $title   = (string) $io->getArgument('title');

        if (!$repo) {
            // Github config
            $configPath = getcwd() . '/.git/config';

            $config = Collection::from(
                str_replace(' = ', '=', file_get_contents($configPath)),
                'ini',
                ['process_section' => true]
            );

            $url = $config->getDeep('remote origin.url');

            preg_match('/git\@github\.com\:(.+)\.git/', $url, $matches);

            $repo = $matches[1] ?? '';

            if (!$repo) {
                preg_match('/https\:\/\/github\.com\/(.+)\.git/', $url, $matches);

                $repo = $matches[1] ?? '';
            }
        }

        if (!$repo || !str_contains($repo, '/')) {
            throw new \UnexpectedValueException('Unknown GitHub repository: ' . $repo);
        }

        $keyPath = $io->getOption('path') ?: $this->getKeyPath($repo);

        Filesystem::mkdir(dirname($keyPath));

        [$account, $repo] = explode('/', $repo, 2);

        if ($title === '') {
            $title = $this->envService->getComputerNameOrAsk($io, 'Key title: ');
        }

        $refresh = $io->getOption('refresh');

        if (!Environment::isWindows()) {
            $r = $this->app->runProcess('ssh-add')->getExitCode();

            if ($r === 2) {
                $io->writeln('Please run `<info>eval $(ssh-agent)</info>` first.');
                return 1;
            }
        }

        // Add ssh-agent
        $this->appendToProfile('eval $(ssh-agent)');

        if ($refresh && is_file($keyPath)) {
            // Delete ssh cache
            $this->app->runProcess(
                sprintf('ssh-add -d "%s"', $keyPath),
                getcwd()
            );
        }

        $this->app->runCommand(
            'ssh-key',
            [
                'rsa_file' => $keyPath,
                '-m' => true,
                '-r' => $refresh,
                '--comment' => "$account/$repo"
            ],
            $io->getOutput()
        );

        $sshAdd = sprintf('ssh-add "%s"', $keyPath);

        // Add ssh cache
        $this->app->runProcess($sshAdd, getcwd());

        $this->appendToProfile($sshAdd);

        $io->newLine();
        $io->writeln('Starting to add Deploy key to GitHub.');
        $io->writeln('<info>Access GitHub...</info>');

        $token = $this->githubService->getStoredToken();

        if (!$token) {
            $token = $this->githubService->deviceAuth($io);
        }

        $this->githubService->auth($token);

        $this->githubService->getClient()->repository()->keys()
            ->create(
                $account,
                $repo,
                ['title' => $title, 'key' => file_get_contents($this->sshService->getRsaPubFile($keyPath))]
            );

        $io->writeln('Deploy key has successfully added to this repository.');

        return 0;
    }

    /**
     * getKeyPath
     *
     * @param string $repo
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getKeyPath(string $repo): string
    {
        $home = $this->envService->getUserDir();

        $path = $home . '/.lyra/ssh/' . $repo . '/id_rsa';

        if (!is_dir(dirname($path))) {
            Filesystem::mkdir(dirname($path));
        }

        return $path;
    }

    /**
     * appendToProfile
     *
     * @param string      $content
     * @param string|null $find
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function appendToProfile(string $content, ?string $find = null): void
    {
        if (!PlatformHelper::isWindows()) {
            $home = $this->envService->getUserDir();

            if (PlatformHelper::isLinux()) {
                $profile = $home . '/.bashrc';
            } else {
                $profile = $home . '/.zshrc';

                if (!is_file($profile)) {
                    $profile = $home . '/.bash_profile';
                }
            }

            $find = $find ?: $content;

            if (!str_contains(file_get_contents($profile), $find)) {
                $this->app->runProcess(sprintf("echo '%s' >> %s", $content, $profile));
            }

            $this->app->runProcess(sprintf('. %s', $profile));
        }
    }
}
