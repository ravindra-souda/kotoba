<?php

declare(strict_types=1);

namespace App\Document\Trait;

use App\Document\Card;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait KanjiTrait
{
    public const KANJI_MAXLENGTH = 10;

    public const VALIDATION_ERR_KANJI =
        'must be written using only kanji '.
        'with optional hiragana ending (okurigana)';

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
        if (null === $string || '' === $string) {
            return true;
        }

        // must be kanji only or kanji with hiragana ending
        return 1 === preg_match('/^[おご]?\p{Han}+\p{Hiragana}*$/um', $string);
    }

    public function setKanji(?string $kanji): static
    {
        return $this->setLowerAndTrimmedOrNull('kanji', $kanji);
    }

    #[Assert\Callback]
    public function validateKanji(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidKanji($this->kanji)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_KANJI)
            ->atPath('kanji')
            ->addViolation()
        ;
    }
}
