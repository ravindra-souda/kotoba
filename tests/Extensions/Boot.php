<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;

class Boot implements BeforeFirstTestHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {
        exec('bin/console doctrine:mongodb:schema:drop --env=test');
    }

    public function executeAfterLastTest(): void {}
}
