<?php

declare(strict_types=1);

namespace App\Document\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class DeckInput
{
    /** Must be unique */
    #[Groups(['write'])]
    public string $title = '';

    /** Long Description */
    #[Groups(['write'])]
    public ?string $description = null;

    /** An array of cards IRIs */
    #[Groups(['write'])]
    public array $cards = [];

    /** 'any' removes restrictions */
    #[Groups(['write'])]
    public string $type = 'any';

    /** rgba color in hex format */
    #[Groups(['write'])]
    public ?string $color = '#ffffffff';
}
