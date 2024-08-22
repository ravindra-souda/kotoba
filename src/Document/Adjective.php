<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    routePrefix: '/cards',
    operations: [
        new Post(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\AdjectiveRepository')]
class Adjective extends Card
{
    use Trait\GroupTrait;
    use Trait\HiraganaTrait;
    use Trait\KanjiTrait;
    use Trait\KatakanaTrait;
    use Trait\MeaningTrait;
    use Trait\RomajiTrait;

    public const I_ADJECTIVE = 'i';

    public const NA_ADJECTIVE = 'na';

    public const ALLOWED_GROUPS = [
        self::I_ADJECTIVE,
        self::NA_ADJECTIVE,
    ];

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    public const ROMAJI_MAXLENGTH = 50;

    public const ERR_INCORRECT_GROUP = 'Incorrect group set';

    public const ERR_NO_BASE = 'Kanji, hiragana or katakana must be set';

    public const VALIDATION_ERR_I_ADJECTIVE = [
        1 => 'i-adjective must have a hiragana field ending with い',
        2 => 'i-adjective must have a kanji field ending with い',
    ];

    /**
     * Filled by the API.
     *
     * @var array<string,array<string,string>>
     */
    #[Assert\Type(
        type: 'array',
        message: Card::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    #[Groups(['read'])]
    #[MongoDB\Field(type: 'hash')]
    protected array $inflections = [
        'non-past' => [
            'affirmative' => '',
            'negative' => '',
        ],
        'past' => [
            'affirmative' => '',
            'negative' => '',
        ],
    ];

    /**
     * @return array<string,array<string,string>>
     */
    public function getInflections(): array
    {
        return $this->inflections;
    }

    /**
     * @param array<string,array<string,string>> $inflections
     */
    public function setInflections(array $inflections): Adjective
    {
        return $this->setLowerAndTrimmedOrNull('inflections', $inflections);
    }

    public function conjugate(): Adjective
    {
        if (0 !== $this->isValidGroup()) {
            throw new \Exception(self::ERR_INCORRECT_GROUP);
        }

        $base = $this->kanji ?? $this->hiragana ?? $this->katakana;

        if (null === $base) {
            throw new \Exception(self::ERR_NO_BASE);
        }

        $inflections = [];
        if (self::NA_ADJECTIVE === $this->group) {
            $inflections = [
                'non-past' => [
                    'affirmative' => $base,
                    'negative' => $base.'じゃない',
                ],
                'past' => [
                    'affirmative' => $base.'でした',
                    'negative' => $base.'じゃなかった',
                ],
            ];
        }

        if (self::I_ADJECTIVE === $this->group) {
            $root = mb_substr($base, 0, -1);

            // いい adjective is an exception
            if ('い' === $root) {
                $root = 'よ';
            }

            $inflections = [
                'non-past' => [
                    'affirmative' => $base,
                    'negative' => $root.'くない',
                ],
                'past' => [
                    'affirmative' => $root.'かった',
                    'negative' => $root.'くなかった',
                ],
            ];
        }

        $this->setInflections($inflections);

        return $this;
    }

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this->fillRomaji()->conjugate();
    }

    /**
     * @return array<string,array<string,array<string>>>
     */
    public static function getFields(): array
    {
        return [
            'string' => [
                'trim' => ['hiragana', 'katakana', 'kanji'],
                'lower+trim' => ['romaji'],
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return $this->romaji;
    }

    public function isValidGroup(): int
    {
        if (self::NA_ADJECTIVE === $this->group) {
            return 0;
        }

        if (!str_ends_with($this->hiragana ?? '', 'い')) {
            return 1;
        }

        if (null !== $this->kanji && !str_ends_with($this->kanji, 'い')) {
            return 2;
        }

        return 0;
    }

    #[Assert\Callback]
    public function validateGroup(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        $errCode = $this->isValidGroup();
        if (0 === $errCode) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_I_ADJECTIVE[$errCode])
            ->atPath('group')
            ->addViolation()
        ;
    }
}
