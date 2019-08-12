<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

// phpcs:disable

use Asika\SimpleConsole\Console;

include_once __DIR__ . '/Console.php';

class Build extends Console
{
    protected $help = <<<HELP
[Usage] php release.php <version> <next_version>

[Options]
    h | help   Show help information
    v          Show more debug information.
HELP;

    /**
     * doExecute
     *
     * @return  bool|mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function doExecute()
    {
        $currentVersion = trim(file_get_contents(__DIR__ . '/../VERSION'));
        $targetVersion = $this->getArgument(0);

        if (!$targetVersion) {
            $targetVersion = static::versionPlus($currentVersion);
        }

        $this->out('Release version: ' . $targetVersion);

        static::writeVersion($targetVersion);

        $this->exec(sprintf('git commit -am "Release version: %s"', $targetVersion));

        $nextVersion = $this->getArgument(1) ?: static::versionPlus($targetVersion, 1, '-dev');

        $this->out('Prepare version: ' . $nextVersion);

        static::writeVersion($nextVersion);

        $this->exec(sprintf('git commit -am "Prepare %s dev."', $nextVersion));

        $this->exec('git push');

        return true;
    }

    /**
     * writeVersion
     *
     * @param string $version
     *
     * @return  bool|int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static function writeVersion(string $version)
    {
        return file_put_contents(static::versionFile(), $version . "\n");
    }

    /**
     * versionFile
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static function versionFile(): string
    {
        return __DIR__ . '/../VERSION';
    }

    /**
     * versionPlus
     *
     * @param string $version
     * @param int    $offset
     * @param string $suffix
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected static function versionPlus(string $version, int $offset = 1, string $suffix = ''): string
    {
        [$version] = explode('-', $version, 2);

        $numbers = explode('.', $version);

        if (!isset($numbers[2])) {
            $numbers[2] = 0;
        }

        $numbers[2] += $offset;

        $version = implode('.', $numbers);

        if ($suffix) {
            $version .= '-' . $suffix;
        }

        return $version;
    }
}