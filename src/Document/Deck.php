<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use App\State\DeckSaveProcessor;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document]
#[MongoDB\HasLifecycleCallbacks]
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

    public const VALIDATION_ERR_EMPTY =
        'cannot be left empty';

    public const VALIDATION_ERR_MAXLENGTH =
        'cannot not be longer than {{ limit }} characters';

    public const VALIDATION_ERR_TYPE =
        'must be one of these: {{ choices }}';

    public const VALIDATION_ERR_COLOR =
        'must be a 8-character hexadecimal color (rgba)';

    public const VALIDATION_ERR_DUPLICATE =
        'another Deck with the same title {{ value }} already exists';

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
        message: self::VALIDATION_ERR_TYPE,
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

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $createdAt = null;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $updatedAt = null;

    /** @var array<string> */
    protected iterable $words;

    #[Groups('read')]
    #[MongoDB\Id(strategy: 'AUTO', type: 'object_id')]
    private string $id;

    public function __construct()
    {
        $this->words = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        return [
            'string' => ['title', 'description'],
            'enum' => [
                'type' => self::ALLOWED_TYPES,
            ],
        ];
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

    #[MongoDB\PrePersist]
    public function setCreatedAt(): Deck
    {
        $this->createdAt = new \DateTimeImmutable();

        return $this;
    }

    public function setDescription(string $description): Deck
    {
        $this->description = $description;

        return $this;
    }

    public function setId(string $id): Deck
    {
        $this->id = $id;

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
