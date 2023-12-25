<?php

declare(strict_types=1);

namespace App\Document;

final class Verb extends Card
{
    public const ALLOWED_GROUPS = [
        'godan',
        'ichidan',
        'irregular',
    ];
}
