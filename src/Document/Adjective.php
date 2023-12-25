<?php

declare(strict_types=1);

namespace App\Document;

final class Adjective extends Card
{
    public const ALLOWED_GROUPS = [
        'i',
        'na',
    ];

    public const VALIDATION_ERR_GROUPS =
        'must be one of these: {{ choices }}';
}
