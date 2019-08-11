<?php
/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

exec('composer', $output, $r);

if ($r === 127) {
    if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
        exit('Please install composer first: https://getcomposer.org/download/');
    }

    system('php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"');
    system('php -r "if (hash_file(\'sha384\', \'composer-setup.php\') === \'48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5\') { echo \'Installer verified\'; } else { echo \'Installer corrupt\'; unlink(\'composer-setup.php\'); } echo PHP_EOL;"');
    system('php composer-setup.php');
    system('php -r "unlink(\'composer-setup.php\');"');

    echo "Move composer.phar to /usr/local/bin/composer\n\n";
    rename(getcwd() . '/composer.phar', '/usr/local/bin/composer');
}

exec('lyra', $output, $r);

if ($r === 127) {
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
        system('source ' . $path);
    }
}

echo "\nInstall LYRASOFT CLI completed.\n\n";
