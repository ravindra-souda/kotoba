<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Deck;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class DecksPutTest extends ApiTestCase
{
    private const PUT_COMPLETE_VALID_DECK = [
        'title' => 'Basic vocab',
        'description' => 'Words for your daily life',
        'type' => 'any',
        'color' => '#A09050B0',
    ];

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
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => '',
        ],
        'title_duplicate' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put title duplicate',
        ],
        'description_maxlength' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put description maxlength',
        ],
        'type' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put unknown type',
            'type' => 'dummy',
        ],
        'color' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put invalid color',
            'color' => '#G1F2F3F4',
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validDeckProvider(): array
    {
        return [
            [
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
            ], [
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
            ], [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 3',
                ],
                self::PUT_VALID_DECKS['type'],
                self::PUT_VALID_DECKS['type'],
                'basic-vocab-3',
            ], [
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
        return [
            [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put title empty',
                ],
                self::PUT_INVALID_DECKS['title_empty'],
                'title: '.Deck::VALIDATION_ERR_EMPTY,
            ], [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put title maxlength',
                ], [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => str_repeat('*', Deck::TITLE_MAXLENGTH + 1),
                ],
                'title: '.str_replace(
                    '{{ limit }}',
                    (string) Deck::TITLE_MAXLENGTH,
                    Deck::VALIDATION_ERR_MAXLENGTH
                ),
            ], [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put title duplicate (before edition)',
                ],
                self::PUT_INVALID_DECKS['title_duplicate'],
                'title: '.str_replace(
                    '{{ value }}',
                    '"'.self::PUT_INVALID_DECKS['title_duplicate']['title'].'"',
                    Deck::VALIDATION_ERR_DUPLICATE
                ),
            ], [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put description maxlength',
                ], [
                    ...self::PUT_INVALID_DECKS['description_maxlength'],
                    'description' => str_repeat(
                        '*',
                        Deck::DESCRIPTION_MAXLENGTH + 1
                    ),
                ],
                'description: '.str_replace(
                    '{{ limit }}',
                    (string) Deck::DESCRIPTION_MAXLENGTH,
                    Deck::VALIDATION_ERR_MAXLENGTH
                ),
            ], [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put unknown type',
                ],
                self::PUT_INVALID_DECKS['type'],
                'type: '.str_replace(
                    '{{ choices }}',
                    '"'.implode('", "', Deck::ALLOWED_TYPES).'"',
                    Deck::VALIDATION_ERR_TYPE
                ),
            ], [
                [
                    ...self::PUT_VALID_DECKS['color'],
                    'title' => 'put invalid color',
                ],
                self::PUT_INVALID_DECKS['color'],
                'color: '.Deck::VALIDATION_ERR_COLOR,
            ],
        ];
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

        if (self::PUT_INVALID_DECKS['title_duplicate'] === $payload) {
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
