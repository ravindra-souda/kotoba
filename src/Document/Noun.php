<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\FetchNounByCode;
use App\Filter\WithBikagoFilter;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(
    SearchFilter::class,
    // hiragana and kanji will be processed through WithBikagoFilter
    properties: [
        'katakana' => 'start',
        'romaji' => 'istart',
    ],
)]
#[ApiFilter(
    OrderFilter::class,
    properties: ['romaji'],
    arguments: ['orderParameterName' => 'order'],
)]
#[ApiFilter(WithBikagoFilter::class)]
#[ApiResource(
    routePrefix: '/cards',
    operations: [
        new Post(),
        new Delete(),
        new Put(
            controller: FetchNounByCode::class,
            uriTemplate: '/nouns/{code}',
            /* bypassing faulty internal document fetching with our custom
               controller */
            read: false
        ),
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/wildcards')
    ]
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
    protected ?string $bikago = null;

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
        return $this->dedupBikago()->fillRomaji();
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

    private function dedupBikago(): self
    {
        if (null === $this->bikago) {
            return $this;
        }

        if (str_starts_with($this->hiragana ?? '', $this->bikago)) {
            $this->setHiragana(
                substr($this->hiragana, strlen($this->bikago))
            );
        }

        if (str_starts_with($this->kanji ?? '', $this->bikago)) {
            $this->setKanji(
                substr($this->kanji, strlen($this->bikago))
            );
        }

        return $this;
    }
}
