<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Github;

use Github\Exception\RuntimeException;
use Lyrasoft\Cli\Environment\EnvironmentHelper;
use Lyrasoft\Cli\Process\RunProcessTrait;
use Lyrasoft\Cli\Service\GithubService;
use Lyrasoft\Cli\Service\SshService;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Prompter\PasswordPrompter;
use Windwalker\Console\Prompter\Prompter;
use Windwalker\DI\Annotation\Inject;
use Windwalker\Environment\PlatformHelper;
use Windwalker\Filesystem\Folder;
use Windwalker\String\Str;
use Windwalker\Structure\Structure;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DeployKeyCommand extends Command
{
    use RunProcessTrait;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'deploy-key';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Add ssh to your Github account.';

    /**
     * The usage to tell user how to use this command.
     *
     * @var string
     *
     * @since  2.0
     */
    protected $usage = '%s <cmd><command></cmd> <repository> <title> <option>[option]</option>';

    /**
     * The manual about this command.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $help;

    /**
     * Property githubService.
     *
     * @Inject()
     *
     * @var GithubService
     */
    protected $githubService;

    /**
     * Property sshService.
     *
     * @Inject()
     *
     * @var SshService
     */
    protected $sshService;

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {
        $this->addOption('r')
            ->alias('refresh')
            ->description('Refresh SSH key.');
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @throws \Exception
     * @since  2.0
     */
    protected function doExecute()
    {
        $repo    = (string) $this->getArgument(0);
        $title   = (string) $this->getArgument(1);

        if (!$repo) {
            // Github config
            $configPath = getcwd() . '/.git/config';

            $config = (new Structure())->loadString(
                str_replace(' = ', '=', file_get_contents($configPath)),
                'ini',
                ['processSections' => true]
            );

            $url = $config->get('remote "origin".url');

            preg_match('/git\@github\.com\:(.+)\.git/', $url, $matches);

            $repo = $matches[1] ?? '';

            if (!$repo) {
                preg_match('/https\:\/\/github\.com\/(.+)\.git/', $url, $matches);

                $repo = $matches[1] ?? '';
            }
        }

        if (!$repo || !Str::contains($repo, '/')) {
            throw new \UnexpectedValueException('Unknown GitHub repository: ' . $repo);
        }

        $keyPath = $this->getOption('path') ?: static::getKeyPath($repo);

        Folder::create(dirname($keyPath));

        [$account, $repo] = explode('/', $repo, 2);

        if ($title === '') {
            if (isset($_SERVER['COMPUTERNAME'])) {
                $title = $_SERVER['COMPUTERNAME'];
            } elseif (gethostname()) {
                $title = gethostname();
            } else {
                $title = Prompter::notNullText('Key Title: ');
            }
        }

        $refresh = $this->getOption('r');

        if (!PlatformHelper::isWindows()) {
            $r = $this->runProcess('ssh-add');

            if ($r === 2) {
                $this->out('Please run `<info>eval $(ssh-agent)</info>` first.');
                $this->console->close(1);
            }
        }

        // Add ssh-agent
        $this->appendToProfile('eval $(ssh-agent)');

        if ($refresh && is_file($keyPath)) {
            // Delete ssh cache
            $this->runProcess(
                sprintf('ssh-add -d "%s"', $keyPath),
                getcwd()
            );
        }

        $this->console->executeByPath(
            ['util', 'ssh-key', $keyPath],
            [
                'm' => 1,
                'r' => $refresh,
                'C' => "$account/$repo"
            ],
            $this->io
        );

        $sshAdd = sprintf('ssh-add "%s"', $keyPath);

        // Add ssh cache
        $this->runProcess($sshAdd, getcwd());

        $this->appendToProfile($sshAdd);

        $this->out()
            ->out('Starting to add Deploy key to GitHub.')
            ->out('<info>Access GitHub...</info>');

        $token = $this->githubService->getStoredToken();

        if (!$token) {
            $token = $this->githubService->deviceAuth($this->getIO());
        }

        $this->githubService->auth($token);

        $this->githubService->getClient()->repository()->keys()
            ->create(
                $account,
                $repo,
                ['title' => $title, 'key' => file_get_contents($this->sshService->getRsaPubFile($keyPath))]
            );

        $this->out('Deploy key has successfully added to this repository.');

        return true;
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
    public static function getKeyPath(string $repo): string
    {
        $home = EnvironmentHelper::getUserDir();

        $path = $home . '/.lyra/ssh/' . $repo . '/id_rsa';

        if (!is_dir(dirname($path))) {
            Folder::create(dirname($path));
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
            if (PlatformHelper::isLinux()) {
                $profile = EnvironmentHelper::getUserDir() . '/.bashrc';
            } else {
                $profile = EnvironmentHelper::getUserDir() . '/.bash_profile';
            }

            $find = $find ?: $content;

            if (strpos(file_get_contents($profile), $find) === false) {
                $this->runProcess(sprintf("echo '%s' >> %s", $content, $profile));
            }

            $this->runProcess(sprintf('. %s', $profile));
        }
    }
}
