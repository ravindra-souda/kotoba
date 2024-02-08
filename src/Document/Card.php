<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Card extends AbstractKotobaDocument
{
    public const VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA =
        'either hiragana or katakana must be filled';

    public const VALIDATION_ERR_NOT_AN_ARRAY =
        'must be a valid array';

    public const VALIDATION_ERR_JLPT =
        'must be an integer between 1 and 5';

    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: self::VALIDATION_ERR_JLPT,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'int')]
    protected ?int $jlpt = 5;

    public function getJlpt(): ?int
    {
        return $this->jlpt;
    }

    public function setJlpt(?int $jlpt): static
    {
        $this->jlpt = $jlpt;

        return $this;
    }
}
