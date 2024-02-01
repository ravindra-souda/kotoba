<?php

declare(strict_types=1);

namespace App\Document\Trait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait RomajiTrait
{
    public const VALIDATION_ERR_ROMAJI =
        'must be written using only roman characters';

    /** Must be written using only roman characters */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Regex(
        pattern: '/^[a-z]+$/i',
        message: self::VALIDATION_ERR_ROMAJI
    )]
    #[Assert\Length(
        max: self::ROMAJI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $romaji = null;

    public function getRomaji(): string
    {
        return $this->romaji;
    }

    public function setRomaji(?string $romaji): static
    {
        return $this->setLowerAndTrimmedOrNull('romaji', $romaji);
    }
}
