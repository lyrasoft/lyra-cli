<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace PHPSTORM_META {

    use Lyrasoft\Cli\Ioc;
    use Windwalker\DI\Container;

    override(
        Container::newInstance(0),
        map([
            '' => '@'
        ])
    );

    override(
        Container::createSharedObject(0),
        map([
            '' => '@'
        ])
    );

    override(
        Container::createObject(0),
        map([
            '' => '@'
        ])
    );

    override(
        Ioc::make(0),
        map([
            '' => '@'
        ])
    );

    override(
        Ioc::get(0),
        map([
            '' => '@'
        ])
    );
}
