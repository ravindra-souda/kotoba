<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(uriTemplate: '/cards/adjectives'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    //processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\AdjectiveRepository')]
class Adjective extends Card
{
    use Trait\GroupTrait,
        Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait;

    public const I_ADJECTIVE = 'i';

    public const NA_ADJECTIVE = 'na';

    public const ALLOWED_GROUPS = [
        self::I_ADJECTIVE,
        self::NA_ADJECTIVE,
    ];

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    public const ERR_INCORRECT_GROUP = 'Incorrect group set';

    public const ERR_NO_BASE = 'Kanji, hiragana or katakana must be set';

    /** Filled by the API */
    #[Assert\Type(
        type: 'array',
        message: Card::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    #[Groups(['read'])]
    #[MongoDB\Field(type: 'hash')]
    protected array $inflections = [
        'non-past' => [
            'affirmative' => '',
            'negative' => ''
        ],
        'past' => [
            'affirmative' => '',
            'negative' => ''
        ]
    ];

    /** Should be set to 'adjective' to create an Adjective flashcard */
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $type = 'adjective';

    public function getInflections(): array
    {
        return $this->inflections;
    }

    public function setInflections(array $inflections): Adjective
    {        
        $this->inflections = $this->trimArrayValues($inflections);

        return $this;
    }

    public function isValidGroup(): bool
    {
        if ($this->group === self::NA_ADJECTIVE) {
            return true;
        }

        if (!str_ends_with($this->hiragana ?? '', 'い')) {
            return false;
        }

        if ($this->kanji !== null && !str_ends_with($this->kanji, 'い')) {
            return false;
        }
        
        if ($this->hiragana === null && $this->katakana !== null) {
            return false;
        }

        return true;
    }

    public function conjugate(): Adjective
    {
        if (!$this->isValidGroup()) {
            throw new \Exception(self::ERR_INCORRECT_GROUP);
        }

        $base = $this->kanji ?? $this->hiragana ?? $this->katakana;

        if ($base === null) {
            throw new \Exception(self::ERR_NO_BASE);
        }

        if ($this->group === self::NA_ADJECTIVE) {
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

        if ($this->group === self::I_ADJECTIVE) {
            $root = mb_substr($base, 0, -1);

            // いい adjective is an exception
            if ($root === 'い') {
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
}
