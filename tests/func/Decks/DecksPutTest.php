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
        'color' => [
            'fixture' => 'color',
            'payload' => [
                'color' => '#G1F2F3F4',
            ],
            'message' => 'color: '.Deck::VALIDATION_ERR_COLOR,
        ],
    ];

    private const PUT_DECKS_WITH_CARDS = [
        'any' => [
            'title' => 'Welcome to the urban jungle',
            'description' => 'Surviving guide to this new city',
            'type' => 'any',
            'color' => '#2f2492e0',
        ],
        'nouns' => [
            'title' => 'Pets',
            'description' => 'Your friendly small companions',
            'type' => 'nouns',
            'color' => '#7c280eb0',
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
        'nouns' => [
            'nouns_pets_1', 'nouns_pets_2', 'nouns_both_1', 'nouns_both_2',
        ],
    ];

    private const CARDS_CLASSES = [
        'adjectives' => Adjective::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];

    private static array $objectIds;

    public static function setUpBeforeClass(): void
    {
        foreach (self::PUT_DECKS_WITH_CARDS as $payload) {
            static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Deck::class);
        }

        foreach (self::PUT_CARDS_ATTACHED_TO_DECKS as $key => $payload) {
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
            static::assertArrayHasKey('id', $content);
            
            static::$objectIds[$key] = $content['id'];
        }
    }

    /**
     * @return array<array<array<string>>>
     */
    public function validDeckProvider(): array
    {
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
     * @return array<array<array<string>>>
     */
    public function invalidDeckProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_DECKS,
            self::PUT_FIXTURE_DECKS
        );
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
