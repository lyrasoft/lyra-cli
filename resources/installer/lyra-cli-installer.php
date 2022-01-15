<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

exec('which composer', $output, $r);

if ($r !== 0) {
    if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
        exit('Please install composer first: https://getcomposer.org/download/');
    }

    // phpcs:ignore
    system('wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet');

    echo "Move composer.phar to /usr/local/bin/composer\n\n";
    rename(getcwd() . '/composer.phar', '/usr/local/bin/composer');
}

exec('which lyra', $output, $r);

if ($r !== 0) {
    // Install LYRA CLI
    system('composer global require lyrasoft/cli');
}

$home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];

if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
    // Windows, No actions
} elseif (PHP_OS === 'Darwin') {
    echo "Add \$HOME/.composer/vendor/bin to PATH\n";

    // Mac
    $path = $home . '/.bash_profile';

    if (strpos(file_get_contents($path), '$HOME/.composer/vendor/bin') === false) {
        system('echo \'export PATH="$PATH:$HOME/.composer/vendor/bin"\' >> ' . $path);
        system('source ' . $path);
    }
} else {
    $composerDir = '.config/composer/vendor/bin';

    if (!is_dir($home . '/' . $composerDir)) {
        $composerDir = '.composer/vendor/bin';
    }

    echo "Add \$HOME/$composerDir to PATH\n";

    // Linux
    $path = $home . '/.bashrc';

    if (strpos(file_get_contents($path), '$HOME/' . $composerDir) === false) {
        system('echo \'export PATH="$PATH:$HOME/' . $composerDir . '"\' >> ' . $path);
        system('. ' . $path);
    }
}

echo "\nInstall LYRASOFT CLI completed.\n\n";
