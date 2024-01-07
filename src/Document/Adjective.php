<?php

declare(strict_types=1);

namespace App\Document;

final class Adjective extends Card
{
    use Trait\HiraganaTrait;

    use Trait\KanjiTrait;

    use Trait\KatakanaTrait;

    public const ALLOWED_GROUPS = [
        'i',
        'na',
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
