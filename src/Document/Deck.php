<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\FetchDeckByCode;
use App\Dto\DeckDto;
use App\State\SaveProcessor;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(
    SearchFilter::class,
    properties: [
        'code' => 'iexact',
        'title' => 'ipartial',
        'description' => 'ipartial',
        'type' => 'iexact',
    ],
)]
#[ApiFilter(
    OrderFilter::class,
    properties: ['title', 'description', 'type'],
    arguments: ['orderParameterName' => 'order'],
)]
#[ApiResource(
    input: DeckDto::class,
    operations: [
        new Post(),
        new Delete(),
        new Put(
            controller: FetchDeckByCode::class,
            uriTemplate: '/decks/{code}',
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
#[MongoDB\Document(repositoryClass: 'App\Repository\DeckRepository')]
#[Unique(fields: ['title'], message: self::VALIDATION_ERR_DUPLICATE)]
class Deck extends AbstractKotobaDocument
{
    public const TITLE_MAXLENGTH = 100;

    public const DESCRIPTION_MAXLENGTH = 500;

    public const ALLOWED_TYPES = [
        'adjectives',
        'any',
        'kana',
        'nouns',
        'verbs',
    ];

    public const VALIDATION_ERR_COLOR =
        'must be a 8-character hexadecimal color (rgba)';

    public const VALIDATION_ERR_DUPLICATE =
        'another Deck with the same title {{ value }} already exists';

    public const VALIDATION_ERR_CARDS_ASSOCIATION =
        '{{ cards }} are not the same type as this Deck';

    /** Must be unique */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::TITLE_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $title = '';

    /** Slugified by the API from the name */
    #[ApiProperty(identifier: true)]
    #[Groups('read')]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    /** Long Description */
    #[Assert\Length(
        max: self::DESCRIPTION_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $description = null;

    /** 'any' removes restrictions */
    #[Assert\Choice(
        choices: self::ALLOWED_TYPES,
        message: self::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $type = 'any';

    /** rgba color in hex format */
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG_WITH_ALPHA,
        message: self::VALIDATION_ERR_COLOR,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $color = '#ffffffff';

    /** @var Collection<int,Card> */
    #[Groups(['read', 'write'])]
    #[MongoDB\ReferenceMany(
        targetDocument: "App\Document\Card", 
        mappedBy:'decks', cascade:['persist'], storeAs:'id'
    )]
    public Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this;
    }

    /**
     * @return array<string,array<string,array<string>>>
     */
    public static function getFields(): array
    {
        return [
            'string' => [
                'trim' => ['title', 'description'],
            ],
            'enum' => [
                'type' => self::ALLOWED_TYPES,
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return $this->title;
    }

    /*
    public function addCard(Card $card): void
    {
        $card->deck = $this;
        $this->cards->add($card);
    }
    */

    public function addCard(Card $card): Deck
    {
        if ($this->cards->contains($card)) {
            return $this;
        }

        $card->addDeck($this);
        $this->cards->add($card);

        return $this;
    }

    /*
    public function removeCard(Card $card): void
    {
        $card->deck = null;
        $this->cards->removeElement($card);
    }
    */

    public function removeCard(Card $card): Deck
    {
        $card->removeDeck($this);
        $this->cards->removeElement($card);

        return $this;
    }

    public function setCode(string $code): Deck
    {
        $this->code = $code;

        return $this;
    }

    public function setColor(string $color): Deck
    {
        $this->color = $color;

        return $this;
    }

    public function setDescription(string $description): Deck
    {
        $this->description = $description;

        return $this;
    }

    public function setTitle(string $title): Deck
    {
        $this->title = $title;

        return $this;
    }

    public function setType(string $type): Deck
    {
        $this->type = $type;

        return $this;
    }
}
