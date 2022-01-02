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
use Lyrasoft\Cli\Helper\ComposerHelper;
use Lyrasoft\Cli\Helper\DevtoolsHelper;
use Lyrasoft\Cli\Services\EnvService;
use Lyrasoft\Cli\Services\GithubService;
use Lyrasoft\Cli\Services\PstormService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IOInterface;
use Windwalker\Filesystem\Filesystem;

/**
 * The SnifferCommand class.
 */
#[CommandWrapper(description: 'Install and enable PHP Sniffer for this project.')]
class SnifferCommand implements CommandInterface
{
    protected IOInterface $io;

    public function __construct(
        protected Application $app,
        protected GithubService $githubService,
        protected EnvService $envService,
        protected PstormService $pstormService,
    ) {
    }

    public function configure(Command $command): void
    {
        $command->addOption(
            'path',
            'p',
            InputOption::VALUE_NONE,
            'Also update phpcs executable path.'
        );

        $command->addOption(
            'ww3',
            '',
            InputOption::VALUE_NONE,
            'Use Windwalker 3 PSR-12 Rules.'
        );
    }

    public function execute(IOInterface $io): int
    {
        $this->io = $io;

        if ($io->getOption('path')) {
            $io->style()->title('Updating phpcs executable file');

            $this->updateBinFile();
        }

        $io->style()->title('Updating current project settings');

        $this->updateSnifferSetting();

        return 0;
    }

    /**
     * updateBinFile
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function updateBinFile(): void
    {
        $idea = getcwd() . '/.idea';

        if (!is_dir($idea)) {
            throw new \RuntimeException('This path is not a PhpStorm project.');
        }

        $this->githubService->prepareRepo();

        $vendorPath = ComposerHelper::getGlobalPath() . '/vendor';

        // Install PHPCS to PhpStorm Settings
        $phpcsPath = $vendorPath . '/bin/phpcs' . ($this->envService->isWindows() ? '.bat' : '');

        $configFile = $idea . '/php.xml';

        if (!is_file($configFile)) {
            Filesystem::write($configFile, '<project />');
        }

        $xml = new \SimpleXMLElement(file_get_contents($configFile));

        // Let's prepare XML deep nodes
        $component = $xml->xpath('//component[@name="PhpCodeSniffer"]')[0] ?? null;

        if (!isset($component)) {
            $component = $xml->addChild('component');
            $component->addAttribute('name', 'PhpCodeSniffer');
        }

        if (!isset($component->phpcs_settings)) {
            $component->addChild('phpcs_settings');
        }

        if (!isset($component->phpcs_settings->PhpCSConfiguration)) {
            $component->phpcs_settings->addChild('PhpCSConfiguration');
        }

        // All nodes prepared, put value.
        $component->phpcs_settings->PhpCSConfiguration['tool_path'] = $phpcsPath;

        // Then save
        $dom = dom_import_simplexml($xml)->ownerDocument;

        $dom->formatOutput = true;

        file_put_contents($configFile, $dom->saveXML());

        $this->io->writeln(sprintf('Update PhpStorm Sniffer Path to: <info>%s</info>', $phpcsPath));
    }

    /**
     * updateSnifferSetting
     *
     * @return  void
     *
     * @throws \Exception
     * @since  __DEPLOY_VERSION__
     */
    protected function updateSnifferSetting(): void
    {
        $idea = getcwd() . '/.idea';

        if (!is_dir($idea)) {
            throw new \RuntimeException('This path is not a PhpStorm project.');
        }

        $configFile = $idea . '/inspectionProfiles/Project_Default.xml';

        if (!is_file($configFile)) {
            Filesystem::write(
                $configFile,
                <<<XML
<component name="InspectionProjectProfileManager">
  <profile version="1.0">
    <option name="myName" value="Project Default" />
  </profile>
</component>
XML
            );
        }

        $xml = new \SimpleXMLElement(file_get_contents($configFile));

        // Let's prepare XML deep nodes
        $tool = $xml->xpath('//profile/inspection_tool[@class="PhpCSValidationInspection"]')[0] ?? null;

        if (!isset($tool)) {
            $tool = $xml->profile->addChild('inspection_tool');
            $tool->addAttribute('class', 'PhpCSValidationInspection');
        }

        $tool['enabled'] = 'true';
        $tool['level'] = 'WEAK WARNING';
        $tool['enabled_by_default'] = 'true';

        if ($this->io->getOption('ww3')) {
            static::addOrCreateOptionWithValue($tool, 'CODING_STANDARD', 'Custom');
            static::addOrCreateOptionWithValue(
                $tool,
                'CUSTOM_RULESET_PATH',
                DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker'
            );
        } else {
            static::addOrCreateOptionWithValue($tool, 'CODING_STANDARD', 'PSR12');
        }

        static::addOrCreateOptionWithValue($tool, 'CHECK_INC', 'false');
        static::addOrCreateOptionWithValue($tool, 'CHECK_JS', 'false');
        static::addOrCreateOptionWithValue($tool, 'CHECK_CSS', 'false');

        // Then save
        $dom = dom_import_simplexml($xml)->ownerDocument;

        $dom->formatOutput = true;

        file_put_contents($configFile, $dom->saveXML());

        $this->io->writeln(
            sprintf(
                'Use <info>%s</info> as phpcs ruleset path.',
                DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker'
            )
        );

        $this->io->writeln('Enable PHP Sniffer for current project.');
    }

    /**
     * addOrCreateOptionWithValue
     *
     * @param  \SimpleXMLElement  $tool
     * @param  string             $name
     * @param  string|null        $value
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function addOrCreateOptionWithValue(\SimpleXMLElement $tool, string $name, string $value = null): void
    {
        $option = $tool->xpath('//option[@name="' . $name . '"]')[0] ?? null;

        if (!isset($option)) {
            $option = $tool->addChild('option');
            $option->addAttribute('name', $name);
        }

        if ($value !== null) {
            $option['value'] = $value;
        }
    }
}
