<?php

declare(strict_types=1);

namespace App\Document\Trait;

use App\Document\{Adjective, Card, Kana, Noun, Verb};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait HiraganaTrait
{
    public const VALIDATION_ERR_HIRAGANA =
        'must be written using only hiragana';

    /** Must be written using only hiragana */
    #[Assert\Length(
        max: self::HIRAGANA_MAXLENGTH,
        maxMessage: Card::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $hiragana = null;

    public function getHiragana(): ?string
    {
        return $this->hiragana;
    }

    public static function isValidHiragana(?string $string): bool
    {
        if ($string === null) {
            return true;
        }

        // must be hiragana only
        return preg_match('/\P{Hiragana}/um', $string) !== 1;
    }

    public function setHiragana(?string $hiragana): Adjective|Kana|Noun|Verb
    {
        $this->hiragana = $this->shapeStr($hiragana);

        return $this;
    }

    public function hasHiraganaOrKatakana(): bool
    {
        if (!property_exists($this, 'katakana')) {
            return $this->hiragana !== null;
        }

        return $this->hiragana !== null || $this->katakana !== null;
    }

    #[Assert\Callback]
    public function validateHasHiraganaOrKatakana(
        ExecutionContextInterface $context, 
        mixed $payload
    ): void
    {
        if ($this->hasHiraganaOrKatakana()) {
            return;
        }

        $context
            ->buildViolation(Card::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA)
            ->atPath('hiragana')
            ->addViolation();

        if (!property_exists($this, 'katakana')) {
            return;
        }

        $context
            ->buildViolation(Card::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA)
            ->atPath('katakana')
            ->addViolation();
    }
}
