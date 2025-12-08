<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\{Adjective, Card, Kana, Kanji, Noun, Verb};

trait CardAssociationTrait
{
    private const CARDS_CLASSES = [
        'adjectives' => Adjective::class,
        'kana' => Kana::class,
        'kanji' => Kanji::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];
    
    private static array $postedCards;

    private static array $cardIris;

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

            $content = json_decode($response->getContent(), true);
            static::assertMatchesResourceItemJsonSchema(Card::class);
            
            // at this stage, decks array is always empty 
            // because no associations have been made yet
            unset($content['decks']);
            unset($content['@context']);

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

    private function getCardsFromIri(array $cards): array
    {
        return $this->getDataFrom($cards, self::$postedCards);
    }

    private static function getIriFromCards(array $cards): array
    {
        return self::getDataFrom($cards, self::$cardIris);
    }

    private static function getDataFrom(array $cards, array $from): array
    {
        array_walk($cards, fn(&$card) => $card = $from[$card]);

        return $cards;
    }
}
