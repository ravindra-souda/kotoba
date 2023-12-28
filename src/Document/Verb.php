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

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        $fields = parent::getFields();
        $fields['enum']['group'] = self::ALLOWED_GROUPS;
        
        return $fields;
    }
}
