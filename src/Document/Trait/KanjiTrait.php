<?php

declare(strict_types=1);

namespace App\Document\Trait;

use App\Document\{Adjective, Card, Noun, Verb};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait KanjiTrait
{
    public const KANJI_MAXLENGTH = 10;

    public const VALIDATION_ERR_KANJI =
        'must be written using only kanji or hiragana';

    /** Must be written using only kanji or kanji with hiragana ending */
    #[Assert\Length(
        max: self::KANJI_MAXLENGTH,
        maxMessage: Card::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $kanji = null;

    public function getKanji(): ?string
    {
        return $this->kanji;
    }

    public static function isValidKanji(?string $string): bool
    {
        if ($string === null || $string === '') {
            return true;
        }

        // must be kanji only or kanji with hiragana ending
        return preg_match('/^\p{Han}+\p{Hiragana}*$/um', $string) === 1;
    }

    public function setKanji(?string $kanji): Adjective|Noun|Verb
    {
        $this->kanji = $this->shapeStr($kanji);

        return $this;
    }
}
