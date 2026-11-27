<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class DropDatabaseBeforeTests implements Extension
{
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        $facade
            ->registerSubscribers(
                new TestsStartedSubscriber(),
            );
    }
}
