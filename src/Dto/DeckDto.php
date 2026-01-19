<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class DeckDto
{
    #[Groups(['write'])]
    public string $title = '';

    #[Groups(['write'])]
    public ?string $description = null;

    #[Groups(['write'])]
    public array $cards = [];

    #[Groups(['write'])]
    public string $type = 'any';

    #[Groups(['write'])]
    public ?string $color = '#ffffffff';
}
