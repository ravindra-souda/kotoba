<?php

declare(strict_types=1);

namespace App\Exception;

use App\Document\AbstractKotobaDocument as Doc;

final class CardsNotFoundException extends \Exception
{
    public const MESSAGE_TEMPLATE = 'cards: {{ cards }} not found';

    public function __construct(array $cards)
    {
        $this->message = Doc::formatMsg(self::MESSAGE_TEMPLATE, $cards);
    }
}
