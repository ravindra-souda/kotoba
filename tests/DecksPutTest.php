<?php

declare(strict_types=1);

namespace App\Tests;

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
                'text' => 'type: '.Deck::VALIDATION_ERR_TYPE,
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
