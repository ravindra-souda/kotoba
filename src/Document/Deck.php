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
use App\Document\Dto\DeckInput;
use App\State\SaveProcessor;
use App\Validator as KotobaAssert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
    /* using a DTO to prevent automatic deserialization, allowing us to apply
       our business logic without throwing HTTP 500 errors 
       -> see src/State/SaveProcessor.php validateCards() */
    input: DeckInput::class,
    operations: [
        new Post(),
        new Delete(),
        new Put(
            /* bypassing faulty internal document fetching with our custom
               state provider 
               -> see src/State/SaveProcessor.php fetchDeck() */
            read: false
        ),
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['deck:read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\DeckRepository')]
#[Unique(fields: ['title'], message: self::VALIDATION_ERR_DUPLICATE)]
class Deck extends AbstractKotobaDocument
{
    public const TITLE_MAXLENGTH = 100;

    public const DESCRIPTION_MAXLENGTH = 500;

    public const DEFAULT_TYPE = 'any';

    public const ALLOWED_TYPES = [
        'adjectives',
        'any',
        'kana',
        'nouns',
        'verbs',
    ];

    public const CARDS_CLASSES = [
        'adjectives' => Adjective::Class,
        'kana' => Kana::Class,
        'kanji' => Kanji::Class,
        'nouns' => Noun::Class,
        'verbs' => Verb::Class,
    ];

    public const ROUTE_PREFIX = '/api/cards/';

    public const VALIDATION_ERR_COLOR =
        'must be a 8-character hexadecimal color (rgba)';

    public const VALIDATION_ERR_DUPLICATE =
        'another Deck with the same title {{ value }} already exists';

    public const VALIDATION_ERR_CARDS_ASSOCIATIONS =
        '{{ cards }} are not the same type as this Deck';

    /** Must be unique */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::TITLE_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['card:read', 'deck:read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $title = '';

    /** Slugified by the API from the name */
    #[ApiProperty(identifier: true)]
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    /** Long Description */
    #[Assert\Length(
        max: self::DESCRIPTION_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['card:read', 'deck:read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $description = null;

    /** 'any' removes restrictions */
    #[Assert\Choice(
        choices: self::ALLOWED_TYPES,
        message: self::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['card:read', 'deck:read', 'write'])]
    #[MongoDB\Field]
    protected string $type = self::DEFAULT_TYPE;

    /** rgba color in hex format */
    #[Assert\CssColor(
        formats: Assert\CssColor::HEX_LONG_WITH_ALPHA,
        message: self::VALIDATION_ERR_COLOR,
    )]
    #[Groups(['card:read', 'deck:read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $color = '#ffffffff';

    /** @var Collection<int,Card> */
    /* only [deck:read] here to avoid circular references: 
    (Deck document containing a cards array showing again our Deck) */
    #[Groups(['deck:read'])]
    #[MongoDB\ReferenceMany(
        targetDocument: Card::class, mappedBy:'decks', storeAs:'id',
        sort: ['slug' => 'asc'],
        // preferred strategy if we want to use sort
        strategy: 'setArray',
    )]
    protected Collection $cards;

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
    public function finalizeTasks(): static
    {
        return $this;
    }

    // called right before deletion, see App\State\SaveProcessor
    public function onDelete(): void
    {
        $this->detachCardsFromDeckBeforeDeletion();
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

    public function addCard(Card $card): static
    {
        if ($this->cards->contains($card)) {
            return $this;
        }

        $card->addDeck($this);
        $this->cards->add($card);

        return $this;
    }

    private function detachCardsFromDeckBeforeDeletion(): void
    {
        $this->cards->map(fn($card) => $card->removeDeck($this));
    }

    private function removeCard(Card $card): void
    {
        $card->removeDeck($this);
        $this->cards->removeElement($card);
    }

    public function clearCards(): static
    {
        $cards = $this->cards->getValues();
        array_walk($cards, [$this, 'removeCard']);

        return $this;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    #[Assert\Callback]
    private function validateCardsAssociations(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->type === self::DEFAULT_TYPE) {
            return;
        }

        $invalidCards = array_filter(
            $this->cards->toArray(), 
            fn($card) => get_class($card) !== self::CARDS_CLASSES[$this->type]
        );

        if (empty($invalidCards)) {
            return;
        }

        $invalidIris = [];
        $invertedCardsClasses = array_flip(self::CARDS_CLASSES);

        foreach ($invalidCards as $invalidCard) {
            $class = $invertedCardsClasses[get_class($invalidCard)];
            $invalidIris[] = 
                self::ROUTE_PREFIX.$class.'/'.$invalidCard->getCode();
        }

        $errMessage = $this->formatMsg(
            self::VALIDATION_ERR_CARDS_ASSOCIATIONS, 
            $this->sortByIri($invalidIris)
        );

        $context
            ->buildViolation($errMessage)
            ->atPath('cards')
            ->addViolation()
        ;
    }
}
