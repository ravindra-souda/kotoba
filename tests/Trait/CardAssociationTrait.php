<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\Adjective;
use App\Document\Card;
use App\Document\Kana;
use App\Document\Kanji;
use App\Document\Noun;
use App\Document\Verb;

/**
 * @phpstan-import-type CardExpectedType from \App\Tests\Types
 */
trait CardAssociationTrait
{
    private const CARDS_CLASSES = [
        'adjectives' => Adjective::class,
        'kana' => Kana::class,
        'kanji' => Kanji::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];

    /** @var array<string,mixed> */
    private static array $postedCards;

    /** @var array<string,string> */
    private static array $cardIris;

    /** @var array<string,array{iri:string,content:array<string,mixed>|string}> */
    private static array $cardsToBeRemoved;

    private static string $reverseMappingCardIri;

    private static function initializeCardsBeforeAllTests(): void
    {
        foreach (self::CARDS_ATTACHED_TO_DECKS as $key => $payload) {
            $path = explode('_', $key, 2)[0];
            $response = static::createClient()->request(
                'POST',
                '/api/cards/'.$path,
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(
                self::CARDS_CLASSES[$path]
            );

            /** @var CardExpectedType $content */
            $content = json_decode($response->getContent(), true);
            static::assertMatchesResourceItemJsonSchema(Card::class);

            // at this stage, decks array is always empty
            // because no associations have been made yet
            unset($content['decks'], $content['@context']);

            $iri = $content['@id'];
            self::$postedCards[$iri] = $content;
            self::$cardIris[$key] = $iri;

            if (str_ends_with($key, '_to_delete')) {
                self::$cardsToBeRemoved[$key]['iri'] = $iri;
                self::$cardsToBeRemoved[$key]['content'] = $content;
            }

            if (str_ends_with($key, '_reverse_mapping')) {
                self::$reverseMappingCardIri = $iri;
            }
        }

        foreach (self::$decksWithAssociations as $deck => $cards) {
            $cards = self::CARDS_ASSOCIATIONS[$deck];
            $iris = self::getIriFromCards($cards);
            self::$decksWithAssociations[$deck]['cards'] = $iris;
        }
    }

    /**
     * @param array<string> $cards
     *
     * @return list<CardExpectedType>
     */
    private function getCardsFromIri(array $cards): array
    {
        /** @var list<CardExpectedType> */
        return $this->getDataFrom($cards, self::$postedCards);
    }

    /**
     * @param array<string> $cards
     *
     * @return list<string>
     */
    private static function getIriFromCards(array $cards): array
    {
        /** @var list<string> */
        return self::getDataFrom($cards, self::$cardIris);
    }

    /**
     * @param array<string>                         $cards
     * @param array<string,CardExpectedType|string> $from
     *
     * @return list<CardExpectedType|string>
     */
    private static function getDataFrom(array $cards, array $from): array
    {
        array_walk($cards, fn (&$card) => $card = $from[$card]);

        return $cards;
    }
}
