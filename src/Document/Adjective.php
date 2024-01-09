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

    public const ALLOWED_GROUPS = [
        'i',
        'na',
    ];
}
