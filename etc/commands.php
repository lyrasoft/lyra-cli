<?php

/**
 * Part of cli project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

return [
    'ssh-key' => \Lyrasoft\Cli\Command\SshKeyCommand::class,

    // Github
    'github:add-ssh' => \Lyrasoft\Cli\Command\Github\AddSshCommand::class,
    'github:deploy-key' => \Lyrasoft\Cli\Command\Github\DeployKeyCommand::class,
    'github:token' => \Lyrasoft\Cli\Command\Github\TokenCommand::class,
];
