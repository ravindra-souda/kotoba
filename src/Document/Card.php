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
use App\State\DeckSaveProcessor;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
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
    processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\DeckRepository')]
abstract class Card extends AbstractKotobaDocument
{
    public const ROMAJI_MAXLENGTH = 50;

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    public const KANJI_MAXLENGTH = 10;

    public const ALLOWED_MEANING_LANGS = [
        'en',
        'fr',
    ];

    public const ALLOWED_TYPES = [
        'adjective',
        'kana',
        'noun',
        'verb',
    ];

    public const VALIDATION_ERR_EMPTY =
        'cannot be left empty';
    
    public const VALIDATION_ERR_ROMAJI =
        'must be written using only roman characters';
    
    public const VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA =
        'at least one of these fields must be filled';

    public const VALIDATION_ERR_HIRAGANA =
        'must be written using only hiragana';

    public const VALIDATION_ERR_KATAKANA =
        'must be written using only katakana';
    
    public const VALIDATION_ERR_KANJI =
        'must be written using only kanji or hiragana';

    public const VALIDATION_ERR_MAXLENGTH =
        'cannot not be longer than {{ limit }} characters';

    public const VALIDATION_ERR_MEANING =
        'language unknown must be one of these {{ langList }}';

    public const VALIDATION_ERR_NOT_AN_ARRAY =
        'must be a valid array';

    public const VALIDATION_ERR_ENUM =
        'must be one of these: {{ choices }}';

    public const VALIDATION_ERR_JLPT =
        'must be an integer between 1 and 5';

    /** Must be written using only latin characters */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::ROMAJI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $romaji = '';

    /** Slugified by the API from romaji */
    #[ApiProperty(identifier: true)]
    #[Groups('read')]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    /** Must be written using only hiragana */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::HIRAGANA_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $hiragana = null;

    /** Must be written using only katakana or latin 
     *  and with at least one katakana */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::KATAKANA_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $katakana = '';

    /** Must be written using only kanji or kanji with hiragana ending */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::KANJI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $kanji = '';

    #[Assert\Choice(
        choices: self::ALLOWED_TYPES,
        message: self::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $type = '';

    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: self::VALIDATION_ERR_JLPT,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'int')]
    protected ?int $jlpt = 5;

    #[Assert\Type(
        type: 'array',
        message: self::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    protected ?array $meaning = null;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $createdAt = null;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ApiProperty(identifier: false)]
    #[Groups('read')]
    #[MongoDB\Id(strategy: 'AUTO', type: 'object_id')]
    private string $id;

    #[MongoDB\Field(type: 'int')]
    private int $increment;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getHiragana(): ?string
    {
        return $this->hiragana;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKanji(): ?string
    {
        return $this->kanji;
    }

    public function getKatakana(): ?string
    {
        return $this->katakana;
    }

    public function getIncrement(): int
    {
        return $this->increment;
    }

    public function getJlpt(): ?int
    {
        return $this->jlpt;
    }

    public function getRomaji(): string
    {
        return $this->romaji;
    }

    public function getMeaning(): ?array
    {
        return $this->meaning;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        return [
            'string' => ['romaji', 'hiragana', 'katakana', 'kanji'],
            'enum' => [
                'type' => self::ALLOWED_TYPES,
            ],
        ];
    }

    public static function isValidHiragana(?string $string): bool
    {
        if ($string === null) {
            return true;
        }

        // must be hiragana only
        return preg_match('/\P{Hiragana}/um', $string) !== 1;
    }

    public static function isValidKanji(?string $string): bool
    {
        if ($string === null || $string === '') {
            return true;
        }

        // must be kanji only or kanji with hiragana ending
        return preg_match('/^\p{Han}+\p{Hiragana}*$/um', $string) === 1;
    }

    public static function isValidKatakana(?string $string): bool
    {
        if ($string === null || $string === '') {
            return true;
        }

        // can mix katakana and latin but must have at least one katakana
        return preg_match('/[^\p{Katakana}\p{Latin}]/um', $string) !== 1
            && preg_match('/\p{Katakana}/um', $string) === 1
            // half-width katakana are not allowed
            && preg_match('/[\x{FF65}-\x{FF9F}]/um', $string) !== 1
        ;
    }

    public static function isValidMeaning(array|string|null $meaning): bool
    {
        if ($meaning === null || $meaning === '') {
            return true;
        }

        $validMeaning = true;

        foreach(array_keys($meaning) as $userLang) {
            if (!in_array($userLang, self::ALLOWED_MEANING_LANGS)) {
                $validMeaning = false;
                break;
            }
        }
        
        return $validMeaning;
    }

    public function setCode(string $code): Card
    {
        $this->code = $code;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setCreatedAt(\DateTimeImmutable $date): Card
    {
        $this->createdAt = $date;

        return $this;
    }

    public function setHiragana(?string $hiragana): Card
    {
        $this->hiragana = $hiragana;

        return $this;
    }

    public function setId(string $id): Card
    {
        $this->id = $id;

        return $this;
    }

    public function setJlpt(?int $jlpt): Card
    {
        $this->jlpt = $jlpt;

        return $this;
    }

    public function setKanji(?string $kanji): Card
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function setKatakana(?string $katakana): Card
    {
        $this->katakana = $katakana;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setIncrement(int $increment): Card
    {
        $this->increment = $increment;

        return $this;
    }

    public function setmeaning(?array $meaning): Card
    {
        $this->meaning = $meaning;

        return $this;
    }

    public function setType(string $type): Card
    {
        $this->type = $type;

        return $this;
    }

    // see App\EventListener\PreUpdateListener
    public function setUpdatedAt(\DateTimeImmutable $date): Card
    {
        $this->updatedAt = $date;

        return $this;
    }
}
