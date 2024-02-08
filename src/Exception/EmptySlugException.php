<?php

declare(strict_types=1);

namespace App\Exception;

final class EmptySlugException extends \Exception
{
    public function __construct(
        string $message = 'Cannot create slug from an empty value'
    ) {
        $this->message = $message;
    }
}
