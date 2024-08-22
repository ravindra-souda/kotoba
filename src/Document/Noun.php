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
#[MongoDB\Document(repositoryClass: 'App\Repository\NounRepository')]
class Noun extends Card
{
    use Trait\HiraganaTrait;
    use Trait\KanjiTrait;
    use Trait\KatakanaTrait;
    use Trait\MeaningTrait;
    use Trait\RomajiTrait;

    public const ALLOWED_BIKAGO = [
        'お',
        'ご',
    ];

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    public const ROMAJI_MAXLENGTH = 50;

    /** Must be written using only hiragana */
    #[Assert\Choice(
        choices: self::ALLOWED_BIKAGO,
        message: self::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $bikago;

    public function getBikago(): ?string
    {
        return $this->bikago;
    }

    public function setBikago(?string $bikago): Noun
    {
        $this->bikago = $bikago;

        return $this;
    }

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this->fillRomaji();
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
            'enum' => [
                'bikago' => self::ALLOWED_BIKAGO,
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return $this->romaji;
    }
}
