<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


//#[MongoDB\MappedSuperclass]
#[MongoDB\Document]
/*
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'put', 'delete'],
)]
*/
#[MongoDB\InheritanceType('SINGLE_COLLECTION')]
#[MongoDB\DiscriminatorField('type')]
/*
#[MongoDB\DiscriminatorMap([
    'adjective' => "App\Document\Adjective",
    'kana' => "App\Document\Kana",
    'kanji' => "App\Document\Kanji",
    'noun' => "App\Document\Noun",
    'verb' => "App\Document\Verb",
])]
*/
#[MongoDB\DiscriminatorMap([
    'adjective' => Adjective::class,
    'kana' => Kana::class,
    'kanji' => Kanji::class,
    'noun' => Noun::class,
    'verb' => Verb::class,
])]

abstract class Card extends AbstractKotobaDocument
{
    public const VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA =
        'either hiragana or katakana must be filled';

    public const VALIDATION_ERR_NOT_AN_ARRAY =
        'must be a valid array';

    public const VALIDATION_ERR_JLPT =
        'must be an integer between 1 and 5';

    /** Slugified by the API from reference field (romaji or kanji) */
    #[ApiProperty(identifier: true)]
    #[Groups('read')]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: self::VALIDATION_ERR_JLPT,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'int')]
    protected ?int $jlpt = 5;

    #[MongoDB\ReferenceMany(
        targetDocument: Deck::class, inversedBy:'cards', storeAs:'id'
    )]
    protected Collection $decks;

    public function __construct()
    {
        $this->decks = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): Card
    {
        $this->code = $code;

        return $this;
    }

    public function getJlpt(): ?int
    {
        return $this->jlpt;
    }

    public function setJlpt(?int $jlpt): Card
    {
        $this->jlpt = $jlpt;

        return $this;
    }

    public function addDeck(Deck $deck): Card
    {
        if ($this->decks->contains($deck)) {
            return $this;
        }

        $this->decks->add($deck);

        return $this;
    }

    public function removeDeck(Deck $deck): Card
    {
        $this->decks->removeElement($deck);

        return $this;
    }
}
