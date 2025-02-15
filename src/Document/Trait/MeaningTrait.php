<?php

declare(strict_types=1);

namespace App\Document\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Document\Card;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait MeaningTrait
{
    public const VALIDATION_ERR_MEANING = [
        1 => 'mandatory language "{{ mandLang }}" missing',
        2 => 'language unknown must be one of these: {{ langList }}',
        3 => 'each value must be an non empty array',
    ];
    /*  First value will be the mandatoryLang. Every meaning array
        must have this key filled with a non empty value to be valid
    */
    public const ALLOWED_LANGS = [
        'en',
        'fr',
    ];

    /**
     * 'en' key is mandatory and must have a non-empty array as a value.
     *
     * @var array<string,array<string>>
     */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Groups(['read', 'write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => [
                'en' => ['high; tall', 'expensive; high-priced'],
                'fr' => ['haut; grand', 'coûteux; cher'],
            ],
        ]
    )]
    #[MongoDB\Field(type: 'hash')]
    protected array $meaning = [
        'en' => [''],
        'fr' => [''],
    ];

    /**
     * @return array<string>
     */
    public static function getAllowedLangs(): array
    {
        return self::ALLOWED_LANGS;
    }

    public static function getMandatoryLang(): string
    {
        return self::ALLOWED_LANGS[0];
    }

    /**
     * @return array<string,array<string>>
     */
    public function getMeaning(): array
    {
        return $this->meaning;
    }

    /**
     * @param array<string,array<string>> $meaning
     */
    public static function isValidMeaning(array|string|null $meaning): int
    {
        if (null === $meaning || '' === $meaning) {
            return 0;
        }

        if (!isset($meaning[self::getMandatoryLang()])) {
            return 1;
        }

        foreach ($meaning as $userLang => $userMeaning) {
            if (!in_array($userLang, self::ALLOWED_LANGS)) {
                return 2;
            }
            if (!is_array($userMeaning) || '' === trim($userMeaning[0] ?? '')) {
                return 3;
            }
        }

        return 0;
    }

    /**
     * @param array<string,array<string>> $meaning
     */
    public function setMeaning(array $meaning): static
    {
        return $this->setLowerAndTrimmedOrNull('meaning', $meaning, false);
    }

    #[Assert\Callback]
    public function validateMeaning(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        $errCode = $this->isValidMeaning($this->meaning);
        if (0 === $errCode) {
            return;
        }

        $errMessages = [
            1 => self::formatMsg(
                self::VALIDATION_ERR_MEANING[1],
                self::getMandatoryLang()
            ),
            2 => self::formatMsg(
                self::VALIDATION_ERR_MEANING[2],
                self::getAllowedLangs()
            ),
            3 => self::VALIDATION_ERR_MEANING[3],
        ];

        $context
            ->buildViolation($errMessages[$errCode])
            ->atPath('meaning')
            ->addViolation()
        ;
    }
}
