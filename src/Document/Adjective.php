<?php

declare(strict_types=1);

namespace App\Document;

final class Adjective extends Card
{
    use Trait\GroupTrait,
        Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait;

    public const I_ADJECTIVE = 'i';

    public const NA_ADJECTIVE = 'na';

    public const ALLOWED_GROUPS = [
        self::I_ADJECTIVE,
        self::NA_ADJECTIVE,
    ];
}
