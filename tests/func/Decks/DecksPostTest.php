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
class DecksPostTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

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

    private const UNIQUE_TITLE = 'duplicate deck';

    private const POST_INVALID_DECKS = [
        'title_empty' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => '',
            ],
            'message' => 'title: '.Deck::VALIDATION_ERR_EMPTY,
        ],
        'title_maxlength' => [
            'payload' => self::POST_COMPLETE_VALID_DECK,
            'maxlength' => [
                'title' => '*',
            ],
            'message' => [
                'text' => 'title: '.Deck::VALIDATION_ERR_MAXLENGTH,
                'values' => Deck::TITLE_MAXLENGTH,
            ],
        ],
        'title_duplicate' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => self::UNIQUE_TITLE,
            ],
            'message' => [
                'text' => 'title: '.Deck::VALIDATION_ERR_DUPLICATE,
                'values' => [self::UNIQUE_TITLE],
            ],
        ],
        'description_maxlength' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => 'A deck with a long description',
            ],
            'maxlength' => [
                'description' => '*',
            ],
            'message' => [
                'text' => 'description: '.Deck::VALIDATION_ERR_MAXLENGTH,
                'values' => Deck::DESCRIPTION_MAXLENGTH,
            ],
        ],
        'type' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => 'A deck with a dummy type',
                'type' => 'dummy',
            ],
            'message' => [
                'text' => 'type: '.Deck::VALIDATION_ERR_ENUM,
                'values' => Deck::ALLOWED_TYPES,
            ],
        ],
        'color' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => 'A deck with crazy colors',
                'color' => '#GG00112233',
            ],
            'message' => 'color: '.Deck::VALIDATION_ERR_COLOR,
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
            'complete_deck' => [
                self::POST_COMPLETE_VALID_DECK,
                self::POST_COMPLETE_EXPECTED_DECK,
                'my-first-ten-animals',
            ],
            'minimal_deck' => [
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
        return $this->buildPostProvider(self::POST_INVALID_DECKS);
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

        if (self::UNIQUE_TITLE === $payload['title']) {
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
