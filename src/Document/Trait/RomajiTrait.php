<?php

declare(strict_types=1);

namespace App\Document\Trait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait RomajiTrait
{
    use Script\ScriptTrait;

    public const VALIDATION_ERR_ROMAJI =
        'must be written using only roman characters';

    /** Must be written using only roman characters */
    #[Assert\Regex(
        pattern: '/^[a-zāūēō]+$/i',
        message: self::VALIDATION_ERR_ROMAJI
    )]
    #[Assert\Length(
        max: self::ROMAJI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $romaji = null;

    public function getRomaji(): ?string
    {
        return $this->romaji;
    }

    public function setRomaji(?string $romaji): static
    {
        $romaji = str_ireplace(
            ['aa', 'uu', 'ee', 'oo'],
            ['ā', 'ū', 'ē', 'ō'],
            $romaji
        );

        return $this->setLowerAndTrimmedOrNull('romaji', $romaji);
    }

    private function fillRomaji(): static
    {
        $this->romaji ??= $this->toRomaji($this->hiragana ?? $this->katakana);

        return $this;
    }
}
