<?php

declare(strict_types=1);

namespace App\Document;

final class Kanji extends Card
{
    public const VALIDATION_ERR_KANJI = 'must be written using only kanji';

    /** Must be written using only kanji */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: 1,
        maxMessage: Card::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $kanji;

    private string $kunyomi;

    private string $onyomi;

    public function getKanji(): string
    {
        return $this->kanji;
    }

    public function getKunyomi(): string
    {
        return $this->kunyomi;
    }

    public function getOnyomi(): string
    {
        return $this->onyomi;
    }

    public static function isValidKanji(string $string): bool
    {
        // must be kanji only
        return preg_match('/^\p{Han}$/um', $string) === 1;
    }

    public function setKanji(string $kanji): Kanji
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function setKunyomi(string $kunyomi): Kanji
    {
        $this->kunyomi = $kunyomi;

        return $this;
    }

    public function setOnyomi(string $onyomi): Kanji
    {
        $this->onyomi = $onyomi;

        return $this;
    }
}
