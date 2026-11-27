<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use PHPUnit\Event\Application\Started;
use PHPUnit\Event\Application\StartedSubscriber;

class TestsStartedSubscriber implements StartedSubscriber
{
    public function notify(Started $event): void
    {
        exec('bin/console doctrine:mongodb:schema:drop --env=test');
    }
}
