<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli;

use Composer\InstalledVersions;
use Lyrasoft\Cli\Event\SymfonyDispatcherWrapper;
use Lyrasoft\Cli\Process\ProcessRunnerInterface;
use Lyrasoft\Cli\Process\ProcessRunnerTrait;
use Lyrasoft\Cli\Provider\AppProvider;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Windwalker\Console\CommendRegistrarTrait;
use Windwalker\DI\Container;
use Windwalker\DI\ContainerAwareTrait;
use Windwalker\Environment\PlatformHelper;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Filesystem\Path;

/**
 * The Application class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Application extends Console implements EventAwareInterface, ProcessRunnerInterface
{
    use EventAwareTrait;
    use CommendRegistrarTrait;
    use ContainerAwareTrait;
    use ProcessRunnerTrait;

    /**
     * ConsoleApplication constructor.
     *
     * @param  Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        parent::__construct(
            'LYRASOFT CLI',
            InstalledVersions::getPrettyVersion('lyrasoft/cli')
        );

        $this->setDispatcher(new SymfonyDispatcherWrapper($this->getEventDispatcher()));
    }

    public function boot(): void
    {
        $this->container->registerServiceProvider(new AppProvider($this));
    }

    public function getProcessOutputCallback(?OutputInterface $output = null): callable
    {
        $output ??= new ConsoleOutput();
        $err    = $output->getErrorOutput();

        return static function ($type, $buffer) use ($err, $output) {
            if (Process::ERR === $type) {
                $err->write($buffer, false);
            } else {
                $output->write($buffer);
            }
        };
    }

    public function copyFile(string $file): Process
    {
        if (PlatformHelper::isWindows()) {
            $file = Path::clean($file);
            return $this->runProcess("type {$file} | clip");
        }

        if (PlatformHelper::isUnix()) {
            return $this->runProcess("cat {$file} | pbcopy");
        }

        throw new \RuntimeException('Copy action currently not support this OS.');
    }

    public function copyText(string $file): Process
    {
        $file = addslashes($file);

        if (PlatformHelper::isWindows()) {
            return $this->runProcess("echo \"{$file}\" | clip");
        }

        if (PlatformHelper::isUnix()) {
            return $this->runProcess("echo \"{$file}\" | pbcopy");
        }

        throw new \RuntimeException('Copy action currently not support this OS.');
    }

    public function runCommand(string $name, array $args, ?OutputInterface $output = null): int
    {
        $command = $this->find($name);
        $input = new ArrayInput($args);

        return $command->run($input, $output ?? new ConsoleOutput());
    }
}
