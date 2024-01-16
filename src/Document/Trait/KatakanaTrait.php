<?php

declare(strict_types=1);

namespace App\Document\Trait;

use App\Document\{Adjective, Card, Kana, Noun, Verb};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait KatakanaTrait
{
    public const KATAKANA_MAXLENGTH = 30;

    public const VALIDATION_ERR_KATAKANA =
        'must be written using only katakana';

    /** Must be written using only katakana or latin 
     *  and with at least one katakana */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
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
        if ($string === null || $string === '') {
            return true;
        }

        // can mix katakana and latin but must have at least one katakana
        return preg_match('/[^\p{Katakana}\p{Latin}]/um', $string) !== 1
            && preg_match('/\p{Katakana}/um', $string) === 1
            // half-width katakana are not allowed
            && preg_match('/[\x{FF65}-\x{FF9F}]/um', $string) !== 1
        ;
    }

    public function setKatakana(?string $katakana): Adjective|Kana|Noun|Verb
    {
        $this->katakana = $this->shapeStr($katakana);

        return $this;
    }
}
