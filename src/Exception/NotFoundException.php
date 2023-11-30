<?php

declare(strict_types=1);

namespace App\Exception;

final class NotFoundException extends \Exception
{
    public function __construct(string $message = 'Not Found')
    {
        $this->message = $message;
    }
}
