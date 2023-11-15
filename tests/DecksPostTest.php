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
class DecksPostTest extends ApiTestCase
{
    private const POST_COMPLETE_VALID_DECK = [
        'title' => '    My first ten animals     ',
        'description' => 'Most common animal names',
        'type' => 'nouns',
        'color' => '#A0B0C0D0',
    ];
    private const POST_COMPLETE_EXPECTED_DECK = [
        ...self::POST_COMPLETE_VALID_DECK,
        'title' => 'My first ten animals',
    ];
    private const POST_MINIMAL_VALID_DECK = [
        'title' => 'Numbers',
    ];
    private const POST_INVALID_DECKS = [
        'title_empty' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => '',
        ],
        'title_maxlength' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => 'very long title',
        ],
        'title_duplicate' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => 'duplicate deck',
        ],
        'description_maxlength' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => 'A deck with a long description',
            'description' => 'very long description',
        ],
        'type' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => 'A deck with a dummy type',
            'type' => 'dummy',
        ],
        'color' => [
            ...self::POST_COMPLETE_VALID_DECK,
            'title' => 'A deck with crazy colors',
            'color' => '#GG00112233',
        ],
    ];

    private const UNIQUE_INCREMENT_DECKS = [
        ['title' => 'to be deleted'],
        ['title' => 'unique increment 1'],
        ['title' => 'unique increment 2'],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validDeckProvider(): array
    {
        return [
            [
                self::POST_COMPLETE_VALID_DECK,
                self::POST_COMPLETE_EXPECTED_DECK,
                'my-first-ten-animals',
            ], [
                self::POST_MINIMAL_VALID_DECK,
                self::POST_MINIMAL_VALID_DECK,
                'numbers',
            ],
        ];
    }

    /**
     * @dataProvider validDeckProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testDecksPostValid(
        array $payload,
        array $expected,
        string $code
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Deck::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('createdAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['createdAt']);
        $this->assertMatchesRegularExpression(
            '/\d+-'.$code.'/',
            $content['code']
        );
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidDeckProvider(): array
    {
        return [
            [
                self::POST_INVALID_DECKS['title_empty'],
                'title: '.Deck::VALIDATION_ERR_EMPTY,
            ],
            [
                [
                    ...self::POST_INVALID_DECKS['title_maxlength'],
                    'title' => str_repeat('*', Deck::TITLE_MAXLENGTH + 1),
                ],
                'title: '.str_replace(
                    '{{ limit }}',
                    (string) Deck::TITLE_MAXLENGTH,
                    Deck::VALIDATION_ERR_MAXLENGTH
                ),
            ],
            [
                [
                    ...self::POST_INVALID_DECKS['description_maxlength'],
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
            ],
            [
                self::POST_INVALID_DECKS['type'],
                'type: '.str_replace(
                    '{{ choices }}',
                    '"'.implode('", "', Deck::ALLOWED_TYPES).'"',
                    Deck::VALIDATION_ERR_TYPE
                ),
            ], [
                self::POST_INVALID_DECKS['color'],
                'color: '.Deck::VALIDATION_ERR_COLOR,
            ], [
                self::POST_INVALID_DECKS['title_duplicate'],
                'title: '.str_replace(
                    '{{ value }}',
                    '"'.
                    self::POST_INVALID_DECKS['title_duplicate']['title'].'"',
                    Deck::VALIDATION_ERR_DUPLICATE
                ),
            ],
        ];
    }

    /**
     * @dataProvider invalidDeckProvider
     *
     * @param array<string> $payload
     */
    public function testDecksPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $payload]
        );

        if (self::POST_INVALID_DECKS['title_duplicate'] === $payload) {
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );
        }

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testGeneratedIncrementMustBeUnique(): void
    {
        $increments = [];
        foreach (array_slice(self::UNIQUE_INCREMENT_DECKS, 0, 2) as $deck) {
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $deck]
            );
            $this->assertResponseStatusCodeSame(201);
            $increments[] = strstr(
                json_decode($response->getContent(), true)['code'],
                '-',
                true
            );
            if (!isset($_id)) {
                $_id = json_decode(
                    $response->getContent(),
                    true
                )['@id'];
            }
        }

        static::createClient()->request(
            'DELETE',
            $_id,
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => self::UNIQUE_INCREMENT_DECKS[2]]
        );
        $this->assertResponseStatusCodeSame(201);

        $increments[] = strstr(
            json_decode($response->getContent(), true)['code'],
            '-',
            true
        );
        $this->assertSame($increments, array_unique($increments));
    }
}
