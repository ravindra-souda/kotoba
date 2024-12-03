<?php

declare(strict_types=1);

namespace App\Document\Trait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait KatakanaTrait
{
    public const VALIDATION_ERR_KATAKANA =
        'must be written using only regular katakana '.
        '(half-width katakana are not allowed)';

    /** Must be written using only katakana or latin
     *  and with at least one katakana */
    #[Assert\Length(
        max: self::KATAKANA_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $katakana = null;

    public function getKatakana(): ?string
    {
        return $this->katakana;
    }

    public static function isValidKatakana(?string $string): bool
    {
        if (null === $string || '' === $string) {
            return true;
        }

        // can mix katakana and latin but must have at least one katakana
        return 1 !== preg_match('/[^\p{Katakana}\p{Latin}]/um', $string)
            && 1 === preg_match('/\p{Katakana}/um', $string)
            // half-width katakana are not allowed
            && 1 !== preg_match('/[\x{FF65}-\x{FF9F}]/um', $string);
    }

    public function setKatakana(?string $katakana): static
    {
        // reset romaji when katakana is updated
        if (null !== $this->katakana) {
            $this->romaji = null;
        }

        return $this->setLowerAndTrimmedOrNull('katakana', $katakana);
    }

    #[Assert\Callback]
    public function validateKatakana(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidKatakana($this->katakana)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_KATAKANA)
            ->atPath('katakana')
            ->addViolation()
        ;
    }
}
