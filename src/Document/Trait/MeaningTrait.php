<?php

declare(strict_types=1);

namespace App\Document\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Document\{Adjective, Card, Kanji, Noun, Verb};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait MeaningTrait
{
    /*  First value will be the mandatoryLang. Every meaning array 
        must have this key filled with a non empty value to be valid
    */
    private const ALLOWED_LANGS = [
        'en',
        'fr',
    ];

    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Assert\Type(
        type: 'array',
        message: Card::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    #[Groups(['read', 'write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => [
                'en' => ['high; tall', 'expensive; high-priced'],
                'fr' => ['haut; grand', 'coûteux; cher'],
            ]
        ]
    )]
    #[MongoDB\Field(type: 'hash')]
    protected array $meaning = [
        'en' => [''],
        'fr' => [''],
    ];

    public static function getAllowedLangs(): array 
    {
        return self::ALLOWED_LANGS;
    }

    public static function getMandatoryLang(): string
    {
        return self::ALLOWED_LANGS[0];
    }

    public function getMeaning(): array
    {
        return $this->meaning;
    }

    public static function isValidMeaning(array|string|null $meaning): bool
    {
        if ($meaning === null || $meaning === '') {
            return false;
        }

        foreach(array_keys($meaning) as $userLang) {
            if (!in_array($userLang, self::ALLOWED_LANGS)) {
                return false;
            }
        }
        
        return !empty($meaning[self::getMandatoryLang()]);
    }

    public function setMeaning(array $meaning): Adjective|Kanji|Noun|Verb
    {        
        $this->meaning = Card::trimArrayValues($meaning);

        return $this;
    }
}
