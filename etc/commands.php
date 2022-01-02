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
    'autocomplete' => \Lyrasoft\Cli\Command\AutoCompleteCommand::class,
    '_completion' => \Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand::class,

    // Github
    'github:add-ssh' => \Lyrasoft\Cli\Command\Github\AddSshCommand::class,
    'github:deploy-key' => \Lyrasoft\Cli\Command\Github\DeployKeyCommand::class,
    'github:token' => \Lyrasoft\Cli\Command\Github\TokenCommand::class,

    // Pstorm
    'pstorm:pull-config' => \Lyrasoft\Cli\Command\Pstorm\PullConfigCommand::class,
    'pstorm:push-config' => \Lyrasoft\Cli\Command\Pstorm\PushConfigCommand::class,
    'pstorm:sniffer' => \Lyrasoft\Cli\Command\Pstorm\SnifferCommand::class,
];
