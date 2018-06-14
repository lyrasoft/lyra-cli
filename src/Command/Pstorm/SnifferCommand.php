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
use Lyrasoft\Cli\PhpStorm\PhpStormHelper;
use Windwalker\Console\Command\Command;

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
        $this->addOption('path')
            ->alias('p')
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
            $vendorPath = ComposerHelper::getVendorPath() . '/vendor';

            // Install PHPCS to PhpStorm Settings
            $phpcsPath = $vendorPath . '/squizlabs/php_codesniffer/scripts/phpcs';

            $phpConfig = PhpStormHelper::getConfigFolder() . '/options/php.xml';

            $xml = new \SimpleXMLElement(file_get_contents($phpConfig));

            // Let's prepare XML deep nodes
            $component = $xml->xpath('//component[@name="PhpCodeSniffer"]')[0];

            if (!isset($component)) {
                $component = $xml->addChild('component');
                $component->addAttribute('name', 'PhpCodeSniffer');
            }

            if (!isset($xml->component->phpcs_settings)) {
                $xml->component->addChild('phpcs_settings');
            }

            if (!isset($xml->component->phpcs_settings->PhpCSConfiguration)) {
                $xml->component->phpcs_settings->addChild('PhpCSConfiguration');
            }

            // All nodes prepared, put value.
            $xml->component->phpcs_settings->PhpCSConfiguration['tool_path'] = $phpcsPath;

            // Then save
            $dom = dom_import_simplexml($xml)->ownerDocument;
            $dom->formatOutput = true;

            file_put_contents($phpConfig, $dom->saveXML());

            $this->out(sprintf('Update PhpStorm Sniffer Path to: <info>%s</info>', $phpcsPath));
        }

        //        GithubHelper::prepareRepo();

        // Now Update .idea sniffer settings
        $idea = getcwd() . '/.idea';

        if (!is_dir($idea)) {
            throw new \RuntimeException('This path is not a PhpStorm project.');
        }

        $configFile = $idea . '/inspectionProfiles/Project_Default.xml';

        $xml = new \SimpleXMLElement(file_get_contents($configFile));

        // Let's prepare XML deep nodes
        $tool = $xml->xpath('//profile/inspection_tool[@class="PhpCSValidationInspection"]')[0];

        if (!isset($tool)) {
            $tool = $xml->profile->addChild('inspection_tool');
            $tool->addAttribute('class', 'PhpCSValidationInspection');
        }

        $tool['enabled'] = 'true';
        $tool['level'] = 'WEAK WARNING';
        $tool['enabled_by_default'] = 'true';

        $option = $tool->xpath('//option[@name="CODING_STANDARD"]')[0];

        if (!isset($option)) {
            $option = $tool->addChild('option');
            $option->addAttribute('name', 'CODING_STANDARD');
        }

        $option['value'] = 'Custom';

        $option = $tool->xpath('//option[@name="CUSTOM_RULESET_PATH"]')[0];

        if (!isset($option)) {
            $option = $tool->addChild('option');
            $option->addAttribute('name', 'CUSTOM_RULESET_PATH');
        }

        $option['value'] = DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker';

        // Then save
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        file_put_contents($configFile, $dom->saveXML());

        $this->out(sprintf('Use <info>%s</info> as phpcs ruleset path.', DevtoolsHelper::getLocalPath() . '/Sniffer/Windwalker'));
        $this->out('Enable PHP Sniffer for current project.');

        return true;
    }
}
