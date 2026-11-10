<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class KotobaApiTestCase extends ApiTestCase
{
    // disables rebooting the kernel on each createClient()
    // API Platform 5.0 will set this value to false by default
    protected static ?bool $alwaysBootKernel = false;
}
