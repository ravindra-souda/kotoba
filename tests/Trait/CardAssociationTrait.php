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
    
    private static array $postedCards = [];

    private static array $cardIRIs = [];

    private static array $cardToBeRemoved = [];

    private static bool $cardsInitializationDone = false;

    private static function initializeCardsBeforeAllTests(): void
    {
        /*
        if (self::cardsInitializationDone) {
            return;
        }
        */
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
            
            unset($content['@context']);
            $iri = $content['@id'];
            self::$postedCards[$iri] = $content;
            self::$cardIRIs[$key] = $iri;

            if ($key === 'nouns_both_1') {
                self::$cardToBeRemoved['path'] = $iri;
                self::$cardToBeRemoved['id'] = $content['id'];
            }
        }

        foreach (self::$decksWithAssociations as $deck => $cards) {
            $cards = self::CARDS_ASSOCIATIONS[$deck];
            array_walk($cards, fn (&$card) => $card = self::$cardIRIs[$card]);
            self::$decksWithAssociations[$deck]['cards'] = $cards;
        }
    }
}
