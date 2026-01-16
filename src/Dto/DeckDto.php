<?php

declare(strict_types=1);

namespace App\Dto;

use App\Document\Deck;
use Symfony\Component\Serializer\Annotation\Groups;

final class DeckDto
{
    #[Groups(['write'])]
    public string $title = '';

    #[Groups(['write'])]
    public array $cards = [];
}
