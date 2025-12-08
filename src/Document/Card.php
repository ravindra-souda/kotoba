<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [],
    // default prefix "/cards" will conflict with GET "/cards/{classname}"
    routePrefix: '/dont-call-me-im-just-here-to-deal-with-the-cards-collection',
)]
#[MongoDB\Document]
#[MongoDB\InheritanceType('SINGLE_COLLECTION')]
#[MongoDB\DiscriminatorField('type')]
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
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: self::VALIDATION_ERR_JLPT,
    )]
    #[Groups(['card:read', 'deck:read', 'write'])]
    #[MongoDB\Field(type: 'int')]
    protected ?int $jlpt = 5;

    /* only [card:read] here to avoid circular references: 
    (Card document containing a decks array showing again our Card) */
    #[Groups(['card:read'])]
    #[MongoDB\ReferenceMany(
        targetDocument: Deck::class, inversedBy:'cards', storeAs:'id',
        sort: ['slug' => 'asc'],
        // preferred strategy if we want to use sort
        strategy: 'setArray',
    )]
    protected Collection $decks;

    public function __construct()
    {
        $this->decks = new ArrayCollection();
    }

    public function getDecks(): Collection
    {        
        return $this->decks;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getJlpt(): ?int
    {
        return $this->jlpt;
    }

    public function setJlpt(?int $jlpt): static
    {
        $this->jlpt = $jlpt;

        return $this;
    }

    public function addDeck(Deck $deck): static
    {
        if ($this->decks->contains($deck)) {
            return $this;
        }

        $this->decks->add($deck);

        return $this;
    }

    public function removeDeck(Deck $deck): void
    {
        $this->decks->removeElement($deck);
    }
}
