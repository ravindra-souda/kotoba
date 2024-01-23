<?php

declare(strict_types=1);

namespace App\Document\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Document\{Adjective, Card, Kanji, Noun, Verb};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait MeaningTrait
{
    /*  First value will be the mandatoryLang. Every meaning array 
        must have this key filled with a non empty value to be valid
    */
    private const ALLOWED_LANGS = [
        'en',
        'fr',
    ];

    public const VALIDATION_ERR_MEANING = [
        1 => 'language unknown must be one of these: {{ langList }}',
        2 => 'mandatory language "{{ mandLang }}" missing'
    ];
    
    /** 'en' key is mandatory and must have a non-empty array as a value */
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

    public static function isValidMeaning(array|string|null $meaning): int
    {
        if ($meaning === null || $meaning === '') {
            return 0;
        }

        foreach(array_keys($meaning) as $userLang) {
            if (!in_array($userLang, self::ALLOWED_LANGS)) {
                return 1;
            }
        }
        
        return empty($meaning[self::getMandatoryLang()]) ? 2 : 0;
    }

    public function setMeaning(array $meaning): Adjective|Kanji|Noun|Verb
    {        
        $this->meaning = Card::trimArrayValues($meaning);

        return $this;
    }

    #[Assert\Callback]
    public function validateMeaning(
        ExecutionContextInterface $context, 
        mixed $payload
    ): void
    {
        $errCode = $this->isValidMeaning($this->meaning);
        if ($errCode === 0) {
            return;
        }

        $errMessages = str_replace(
            ['{{ langList }}', '{{ mandLang }}'],
            [
                '"'.implode('", "', self::getAllowedLangs()).'"', 
                self::getMandatoryLang()
            ],
            self::VALIDATION_ERR_MEANING
        );

        $context
            ->buildViolation($errMessages[$errCode])
            ->atPath('meaning')
            ->addViolation();
    }
}
