<?php
/**
 * Part of lyra-cli project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Lyrasoft\Cli\Command\Pstorm;

use Lyrasoft\Cli\Composer\ComposerHelper;
use Lyrasoft\Cli\Github\DevtoolsHelper;
use Lyrasoft\Cli\Github\GithubHelper;
use Lyrasoft\Cli\Ioc;
use Lyrasoft\Cli\PhpStorm\PhpStormHelper;
use Windwalker\Console\Command\Command;
use Windwalker\Environment\Environment;
use Windwalker\Filesystem\File;

/**
 * The PushConfigCommand class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SnifferCommand extends Command
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'sniffer';

    /**
     * Property description.
     *
     * @var  string
     */
    protected $description = 'Install and enable PHP Sniffer for this project.';

    /**
     * The manual about this command.
     *
     * @var  string
     *
     * @since  2.0
     */
    protected $help;

    /**
     * Initialise command.
     *
     * @return void
     *
     * @since  2.0
     */
    protected function init()
    {
        $this->addOption('p')
            ->alias('path')
            ->description('Also update phpcs executable path.');
    }

    /**
     * Execute this command.
     *
     * @return int
     *
     * @since  2.0
     * @throws \LogicException
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        if ($this->getOption('p')) {
            $this->out()
                ->out('Updating phpcs executable file')
                ->out('---------------------------------------------');

            $this->updateBinFile();
        }

        // Now Update .idea sniffer settings
        $this->out()
            ->out('Updating current project settings')
            ->out('---------------------------------------------');

        $this->updateSnifferSetting();

        return true;
    }

    /**
     * updateBinFile
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function updateBinFile()
    {
        $idea = getcwd() . '/.idea';

        if (!is_dir($idea)) {
            throw new \RuntimeException('This path is not a PhpStorm project.');
        }

        GithubHelper::prepareRepo();

        /** @var Environment $env */
        $env = Ioc::get(Environment::class);

        $vendorPath = ComposerHelper::getVendorPath() . '/vendor';

        // Install PHPCS to PhpStorm Settings
        $phpcsPath = $vendorPath . '/bin/phpcs' . ($env->platform->isWin() ? '.bat' : '');

        $configFile = $idea . '/php.xml';

        $xml = new \SimpleXMLElement(file_get_contents($configFile));

        // Let's prepare XML deep nodes
        $component = $xml->xpath('//component[@name="PhpCodeSniffer"]')[0];

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

        $this->out(sprintf('Update PhpStorm Sniffer Path to: <info>%s</info>', $phpcsPath));
    }

    /**
     * updateSnifferSetting
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function updateSnifferSetting()
    {
        $idea = getcwd() . '/.idea';

        if (!is_dir($idea)) {
            throw new \RuntimeException('This path is not a PhpStorm project.');
        }

        $configFile = $idea . '/inspectionProfiles/Project_Default.xml';

        if (!is_file($configFile)) {
            File::write($configFile, <<<XML
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

        $tool['enabled']            = 'true';
        $tool['level']              = 'WEAK WARNING';
        $tool['enabled_by_default'] = 'true';

        static::addOrCreateOptionWithValue($tool, 'CODING_STANDARD', 'Custom');
        static::addOrCreateOptionWithValue(
            $tool,
            'CUSTOM_RULESET_PATH',
            DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker'
        );
        static::addOrCreateOptionWithValue($tool, 'CHECK_INC', 'false');
        static::addOrCreateOptionWithValue($tool, 'CHECK_JS', 'false');
        static::addOrCreateOptionWithValue($tool, 'CHECK_CSS', 'false');

        // Then save
        $dom = dom_import_simplexml($xml)->ownerDocument;

        $dom->formatOutput = true;

        file_put_contents($configFile, $dom->saveXML());

        $this->out(sprintf(
            'Use <info>%s</info> as phpcs ruleset path.',
            DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker')
        );

        $this->out('Enable PHP Sniffer for current project.');
    }

    /**
     * addOrCreateOptionWithValue
     *
     * @param \SimpleXMLElement $tool
     * @param string            $name
     * @param string            $value
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function addOrCreateOptionWithValue(\SimpleXMLElement $tool, string $name, string $value = null)
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
