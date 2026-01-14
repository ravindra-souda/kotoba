<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\{Adjective, Kana, Kanji, Noun, Verb};

trait CardAssociationTrait
{
    private const CARDS_CLASSES = [
        'adjectives' => Adjective::class,
        'kana' => Kana::class,
        'kanji' => Kanji::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];
    
    private array $objectIds;

    private array $cardToBeRemoved;

    private bool $cardsInitializationDone = false;

    private function initializeCardsBeforeAllTests(): void
    {
        if ($this->cardsInitializationDone) {
            return;
        }

        foreach (self::CARDS_ATTACHED_TO_DECKS as $key => $payload) {
            $path = explode('_', $key, 2)[0];
            $response = static::createClient()->request(
                'POST',
                '/api/cards/'.$path,
                ['json' => $payload]
            );

            $this->assertResponseStatusCodeSame(201);
            $this->assertMatchesResourceItemJsonSchema(
                self::CARDS_CLASSES[$path]
            );

            $content = json_decode($response->getContent(), true);
            //var_dump($content);
            $this->assertArrayHasKey('@id', $content);
            
            $this->objectIds[$key] = $content['@id'];

            if ($key === 'nouns_both_1') {
                $this->cardToBeRemoved['path'] = $content['@id'];
                $this->cardToBeRemoved['id'] = $content['id'];
            }
        }

        foreach ($this->decksWithAssociations as $deck => $cards) {
            $cards = self::CARDS_ASSOCIATIONS[$deck];
            array_walk($cards, fn (&$card) => $card = $this->objectIds[$card]);
            $this->decksWithAssociations[$deck]['cards'] = $cards;
        }
        
        $this->cardsInitializationDone = true;
    }
}
