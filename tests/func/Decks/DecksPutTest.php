<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\{Deck, Adjective, Noun, Verb};
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class DecksPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_COMPLETE_VALID_DECK = [
        'title' => 'Basic vocab',
        'description' => 'Words for your daily life',
        'type' => 'any',
        'color' => '#A09050B0',
    ];

    private const PUT_FIXTURE_DECKS = [
        'title_empty' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put title empty',
        ],
        'title_maxlength' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put title maxlength',
        ],
        'title_duplicate' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'placeholder for duplicate title',
        ],
        'description_maxlength' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put description maxlength',
        ],
        'type' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put unknown type',
        ],
        'association_specific' => [
            ...self::PUT_VALID_DECKS['association_specific'],
            'title' => 'put association specific',
        ],
        'color' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put invalid color',
        ],
    ];

    private const UNIQUE_TITLE = 'must be an unique title';

    private const PUT_VALID_DECKS = [
        'title' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => '   Basic vocabulary  ',
        ],
        'description' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'Basic vocab 2',
            'description' => '  Words you need to know before travel ',
        ],
        'type' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'Basic vocab 3',
            'type' => Deck::ALLOWED_TYPES[0],
        ],
        'association_any' => [
            'title' => 'Associations: Welcome to the urban jungle',
            'description' => 'Surviving guide to this new city',
            'type' => 'any',
            'color' => '#2f2492e0',
        ],
        'association_specific' => [
            'title' => 'Associations: Pets',
            'description' => 'Your friendly small companions',
            'type' => 'nouns',
            'color' => '#7c280eb0',
        ],
        'color' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'Basic vocab 4',
            'color' => '#F1F2F3F4',
        ],
    ];

    private const PUT_INVALID_DECKS = [
        'title_empty' => [
            'fixture' => 'title_empty',
            'payload' => [
                'title' => '',
            ],
            'message' => 'title: '.Deck::VALIDATION_ERR_EMPTY,
        ],
        'title_maxlength' => [
            'fixture' => 'title_maxlength',
            'maxlength' => [
                'title' => '*',
            ],
            'message' => [
                'text' => 'title: '.Deck::VALIDATION_ERR_MAXLENGTH,
                'values' => Deck::TITLE_MAXLENGTH,
            ],
        ],
        'title_duplicate' => [
            'fixture' => 'title_duplicate',
            'payload' => [
                'title' => self::UNIQUE_TITLE,
            ],
            'message' => [
                'text' => 'title: '.Deck::VALIDATION_ERR_DUPLICATE,
                'values' => [self::UNIQUE_TITLE],
            ],
        ],
        'description_maxlength' => [
            'fixture' => 'description_maxlength',
            'maxlength' => [
                'description' => '*',
            ],
            'message' => [
                'text' => 'description: '.Deck::VALIDATION_ERR_MAXLENGTH,
                'values' => Deck::DESCRIPTION_MAXLENGTH,
            ],
        ],
        'type' => [
            'fixture' => 'type',
            'payload' => [
                'type' => 'dummy',
            ],
            'message' => [
                'text' => 'type: '.Deck::VALIDATION_ERR_ENUM,
                'values' => Deck::ALLOWED_TYPES,
            ],
        ],
        'association_specific' => [
            'fixture' => 'association_specific',
            'payload' => [
                'cards' => '*',
            ],
            'message' => [
                'text' => 'cards: '.Deck::VALIDATION_ERR_CARDS_ASSOCIATION,
                'values' => '*',
            ],
        ],
        'color' => [
            'fixture' => 'color',
            'payload' => [
                'color' => '#G1F2F3F4',
            ],
            'message' => 'color: '.Deck::VALIDATION_ERR_COLOR,
        ],
    ];

    private const PUT_CARDS_ATTACHED_TO_DECKS = [
        'nouns_city_1' => [
            'hiragana' => 'まち',
            'kanji' => '町',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['town, block, neighbourhood'],
                'fr' => ['ville, quartier, voisinnage'],
            ],
        ],
        'nouns_city_2' => [
            'katakana' => 'コンビニ',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['convenience store'],
                'fr' => ['commerce de proximité, supérette'],
            ],
        ],
        'nouns_pets_1' => [
            'hiragana' => 'うさぎ',
            'kanji' => '兎',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['rabbit'],
            ],
        ],
        'nouns_pets_2' => [
            'katakana' => 'ハムスター',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['hamster'],
            ],
        ],
        'nouns_both_1' => [
            'hiragana' => 'ねこ',
            'kanji' => '猫',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['cat'],
            ],
        ],
        'nouns_both_2' => [
            'hiragana' => 'こいぬ',
            'kanji' => '子犬',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['puppy'],
            ],
        ],
        'verbs_city_1' => [
            'hiragana' => 'はたらく',
            'kanji' => '働く',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => ['to work'],
            ],
            'inflections' => [
                'dictionary' => '働く',
            ],
        ],
        'verbs_city_2' => [
            'hiragana' => 'あるく',
            'kanji' => '歩く',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => ['to walk'],
            ],
            'inflections' => [
                'dictionary' => '歩く',
            ],
        ],
        'adjectives_city_1' => [
            'hiragana' => 'にぎやか',
            'kanji' => '賑やか',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => ['bustling, busy, crowded, lively, prosperous'],
            ],
        ],
    ];

    private const PUT_DECKS_CARDS_ASSOCIATIONS = [
        'any' => [
            'nouns_city_1', 'nouns_city_2', 'nouns_both_1', 'nouns_both_2',
            'verbs_city_1', 'verbs_city_2', 'adjectives_city_1',
        ],
        'specific' => [
            'nouns_pets_1', 'nouns_pets_2', 'nouns_both_1', 'nouns_both_2',
        ],
        'dedup' => [
            'nouns_city_1', 'nouns_city_2', 'nouns_both_1', 'nouns_both_2',
            'nouns_pets_1', 'nouns_pets_2', 'nouns_both_1', 'nouns_both_2',
            'verbs_city_1', 'adjectives_city_1',
            'nouns_pets_2', 'nouns_both_1', 'adjectives_city_1', 'verbs_city_1',
            'verbs_city_2',
        ],
    ];

    private const CARDS_CLASSES = [
        'adjectives' => Adjective::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];

    private array $decksWithAssociations = [
        'any' => [
            ...self::PUT_VALID_DECKS['association_any'],
            'cards' => [],
        ],
        'specific' => [
            ...self::PUT_VALID_DECKS['association_specific'],
            'cards' => [],
        ],
        'dedup' => [
            ...self::PUT_VALID_DECKS['association_any'],
            'title' => 'association dedup',
            'cards' => [],
        ],
    ];

    private array $cardToBeRemoved;

    private bool $cardsInitializationDone = false;

    private function initializeCardsBeforeAllTests(): void
    {
        if ($this->cardsInitializationDone) {
            return;
        }

        $objectIds = [];

        foreach (self::PUT_CARDS_ATTACHED_TO_DECKS as $key => $payload) {
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
            $this->assertArrayHasKey('id', $content);
            
            $objectIds[$key] = $content['id'];

            if ($key === 'nouns_both_1') {
                $this->cardToBeRemoved['path'] = $content['@id'];
                $this->cardToBeRemoved['id'] = $content['id'];
            }
        }

        foreach ($this->decksWithAssociations as $deck => $cards) {
            $names = array_flip(self::PUT_DECKS_CARDS_ASSOCIATIONS[$deck]);
            $ids = array_intersect_key($objectIds, $names);
            $this->decksWithAssociations[$deck]['cards'] = array_values($ids);
        }

        $this->cardsInitializationDone = true;
    }

    /**
     * @return array<array<array<string>>>
     */
    public function validDeckProvider(): array
    {
        self::initializeCardsBeforeAllTests();
        return [
            'title' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 1',
                ],
                self::PUT_VALID_DECKS['title'],
                [
                    ...self::PUT_VALID_DECKS['title'],
                    'title' => 'Basic vocabulary',
                ],
                'basic-vocabulary',
            ],
            'description' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 2',
                ],
                self::PUT_VALID_DECKS['description'],
                [
                    ...self::PUT_VALID_DECKS['description'],
                    'description' => 'Words you need to know before travel',
                ],
                'basic-vocab-2',
            ],
            'type' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 3',
                ],
                self::PUT_VALID_DECKS['type'],
                self::PUT_VALID_DECKS['type'],
                'basic-vocab-3',
            ],
            'association_any' => [
                self::PUT_VALID_DECKS['association_any'],
                $this->decksWithAssociations['any'],
                $this->decksWithAssociations['any'],
                'welcome-to-the-urban-jungle',
            ],
            'association_specific' => [
                self::PUT_VALID_DECKS['association_specific'],
                $this->decksWithAssociations['specific'],
                $this->decksWithAssociations['specific'],
                'pets',
            ],
            'association_dedup' => [
                [
                    ...self::PUT_VALID_DECKS['association_any'],
                    'title' => 'association dedup',
                ],
                $this->decksWithAssociations['dedup'],
                [
                    ...$this->decksWithAssociations['any'],
                    'title' => 'association dedup',
                ],
                'association-dedup',
            ],
            'color' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 4',
                ],
                self::PUT_VALID_DECKS['color'],
                self::PUT_VALID_DECKS['color'],
                'basic-vocab-4',
            ],
        ];
    }

    /**
     * @dataProvider validDeckProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testDecksPutValid(
        array $fixture,
        array $payload,
        array $expected,
        string $code
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];
        $expectedIncrement = strstr(
            json_decode($response->getContent(), true)['code'],
            '-',
            true
        );

        // actual testing
        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Deck::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @depends testDecksPutValid
     */
    public function testDecksAssociationsOrphanRemoval(): void
    {
        $this->initializeCardsBeforeAllTests();
        static::createClient()->request(
            'DELETE',
            $this->cardToBeRemoved['path'],
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?title=associations',
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], 2);
        $this->assertMatchesResourceCollectionJsonSchema(Deck::class);

        foreach ($content['hydra:member'] as $deck) {
            $this->assertNotContains(
                $this->cardToBeRemoved['id'], $deck['cards']
            );
        }
    }

    /**
     * @depends testDecksAssociationsOrphanRemoval
     */
    public function testDecksAssociationsUnknownCard(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/api/decks?title=associations-welcome-to-the-urban-jungle',
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertMatchesResourceCollectionJsonSchema(Deck::class);

        $expected = $content['hydra:member'][0];
        $_id = $content['hydra:member'][0]['@id'];
        $cards = $content['hydra:member'][0]['cards'];

        $cardsWithUnknownCard = $cards[] = $this->cardToBeRemoved['id'];
        $cardsWithUnknownCard = shuffle($cardsWithUnknownCard);
        $payload = $expected;
        $payload['cards'] = $cardsWithUnknownCard;

        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidDeckProvider(): array
    {
        $this->initializeCardsBeforeAllTests();
        $provider = $this->buildPutProvider(
            self::PUT_INVALID_DECKS,
            self::PUT_FIXTURE_DECKS
        );

        $provider['association_specific']['payload']['cards'] = 
            $this->decksWithAssociations['dedup']['cards'];

        /** TODO: message with details on invalid card association */
        return $provider;
    }

    /**
     * @dataProvider invalidDeckProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testDecksPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        if (self::UNIQUE_TITLE === $payload['title']) {
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );

            // needed because request() is asynchronous
            $response->getContent();
        }

        // actual testing
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testDecksPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/decks/dummy',
            [
                'json' => [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Unknown deck',
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDecksPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            [
                'json' => [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'is patch allowed ?',
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        static::createClient()->request(
            'PATCH',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'no mate, patch is not allowed :(',
                ],
            ],
        );
        $this->assertResponseStatusCodeSame(405);
    }
}
