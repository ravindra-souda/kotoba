<?php

declare(strict_types=1);

namespace App\Document\Trait;

use App\Document\Card;
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
        if (null === $string) {
            return true;
        }

        // must be hiragana only
        return 1 !== preg_match('/\P{Hiragana}/um', $string);
    }

    public function setHiragana(?string $hiragana): static
    {
        // reset romaji when hiragana is updated
        if (null !== $this->hiragana) {
            $this->romaji = null;
        }

        return $this->setLowerAndTrimmedOrNull('hiragana', $hiragana);
    }

    public function hasHiraganaOrKatakana(): bool
    {
        if (!property_exists($this, 'katakana')) {
            return null !== $this->hiragana;
        }

        return null !== $this->hiragana || null !== $this->katakana;
    }

    #[Assert\Callback]
    public function validateHasHiraganaOrKatakana(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->hasHiraganaOrKatakana()) {
            return;
        }

        $context
            ->buildViolation(Card::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA)
            ->atPath('hiragana')
            ->addViolation()
        ;

        if (!property_exists($this, 'katakana')) {
            return;
        }

        $context
            ->buildViolation(Card::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA)
            ->atPath('katakana')
            ->addViolation()
        ;
    }

    #[Assert\Callback]
    public function validateHiragana(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidHiragana($this->hiragana)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_HIRAGANA)
            ->atPath('hiragana')
            ->addViolation()
        ;
    }
}
