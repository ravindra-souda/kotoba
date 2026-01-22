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
    use Trait\CardAssociationTrait;

    private const POST_COMPLETE_VALID_DECK = [
        'title' => '    (Post Associations): My first ten animals     ',
        'description' => 'Most common animal names',
        'type' => 'nouns',
        'color' => '#A0B0C0D0',
    ];

    private const POST_COMPLETE_EXPECTED_DECK = [
        ...self::POST_COMPLETE_VALID_DECK,
        'title' => '(Post Associations): My first ten animals',
    ];

    private const POST_MINIMAL_VALID_DECK = [
        'title' => '(Post Associations): Numbers',
    ];

    private const POST_DEDUP_CARDS_DECK = [
        'title' => '(Post Associations): Catch-all',
    ];

    private const DUMMY = [
        'title' => 'dummy',
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
        'association_specific' => [
            'fixture' => 'association_specific',
            'payload' => [
                ...self::POST_COMPLETE_VALID_DECK,
                'title' => 'A deck with wrong cards associations',
                'cards' => '*',
            ],
            'message' => [
                'text' => 'cards: '.Deck::VALIDATION_ERR_CARDS_ASSOCIATION,
                'values' => '*',
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

    private const CARDS_ATTACHED_TO_DECKS = [
        'nouns_animals_1' => [
            'hiragana' => 'いぬ',
            'kanji' => '犬',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['dog'],
            ],
        ],
        'nouns_animals_2' => [
            'hiragana' => 'ねこ',
            'kanji' => '猫',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['cat'],
            ],
        ],
        'nouns_numbers_1' => [
            'hiragana' => 'いち',
            'kanji' => '一',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['one'],
            ],
        ],
        'nouns_both_1' => [
            'hiragana' => 'さかな',
            'kanji' => '魚',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['fish'],
            ],
        ],
        'nouns_both_2' => [
            'hiragana' => 'とり',
            'kanji' => '鳥',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['bird'],
            ],
        ],
        'kanji_numbers_1' => [
            'kanji' => '二',
            'meaning' => [
                'en' => ['two'],
            ],
            'kunyomi' => ['futa', 'futatsu', 'futatabi'],
            'onyomi' => ['ni', 'ji'],
        ],
        'verbs_numbers_1' => [
            'hiragana' => 'かぞえる',
            'kanji' => '数える',
            'jlpt' => 3,
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['to count, to enumerate'],
            ],
            'inflections' => [
                'dictionary' => '数える',
            ],
        ],
        'adjectives_numbers_1' => [
            'hiragana' => 'おおい',
            'kanji' => '多い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => ['many, numerous, a lot'],
            ],
        ],
    ];

    private const CARDS_ASSOCIATIONS = [
        'any' => [
            'nouns_numbers_1', 'kanji_numbers_1', 'nouns_both_1', 
            'nouns_both_2', 'verbs_numbers_1', 'adjectives_numbers_1', 
        ],
        'specific' => [
            'nouns_animals_1', 'nouns_animals_2', 'nouns_both_1', 
            'nouns_both_2',
        ],
        'dedup' => [
            'adjectives_numbers_1', 'nouns_numbers_1', 
            'nouns_both_1', 'nouns_both_1', 'nouns_both_2', 'verbs_numbers_1', 
            'verbs_numbers_1', 'verbs_numbers_1', 'kanji_numbers_1',
        ],
    ];

    private static array $decksWithAssociations = [
        'any' => [
            'cards' => [],
        ],
        'specific' => [
            'cards' => [],
        ],
        'dedup' => [
            'cards' => [],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        fwrite(STDOUT, __METHOD__ . "\n");
        self::initializeCardsBeforeAllTests();
    }

    /**
     * @return array<array<array<string>>>
     */
    public function validDeckProvider(): array
    {
        return [
            'complete_deck' => [
                self::POST_COMPLETE_VALID_DECK,
                'specific',
                self::POST_COMPLETE_EXPECTED_DECK,
                'specific',
                'post-associations-my-first-ten-animals',
            ],
            'minimal_deck' => [
                self::POST_MINIMAL_VALID_DECK,
                'any',
                self::POST_MINIMAL_VALID_DECK,
                'any',
                'post-associations-numbers',
            ],
            'dedup_deck' => [
                self::POST_DEDUP_CARDS_DECK,
                'dedup',
                self::POST_DEDUP_CARDS_DECK,
                'any',
                'post-associations-catch-all',
            ]
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
        string $payloadCardsKey,
        array $expected,
        string $expectedCardsKey,
        string $code
    ): void {
        $payload['cards'] =
            self::$decksWithAssociations[$payloadCardsKey]['cards'];
        $expected['cards'] = 
            self::$decksWithAssociations[$expectedCardsKey]['cards'];

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
     * @depends testDecksPostValid
     */
    public function testDecksAssociationsOrphanRemoval(): void
    {
        // $this->initializeCardsBeforeAllTests();
        static::createClient()->request(
            'DELETE',
            $this->cardToBeRemoved['path'],
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?title=(post associations)',
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], 3);
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
        $payload = [
            ...self::POST_MINIMAL_VALID_DECK,
            'title' => 'post deck unknown card',
            'cards' => $this->decksWithAssociations['any']['cards'],
        ];

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);
        $this->assertNotContains(
            $this->cardToBeRemoved['id'], $content['cards']
        );
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidDeckProvider(): array
    {
        // $this->initializeCardsBeforeAllTests();

        $invalidDecks = self::POST_INVALID_DECKS;
        $cards = self::$decksWithAssociations['dedup']['cards'];
        $invalidDecks['association_specific']['payload']['cards'] = $cards;

        $invalidCards = array_filter(
            self::$cardIRIs,
            fn ($key) => !str_contains($key, 'nouns_'),
            ARRAY_FILTER_USE_KEY
        );
        $invalidIds = array_intersect(
            array_unique($cards),
            $invalidCards
        );

        $invalidDecks['association_specific']['message']['values'] = 
            $invalidIds;
        
        return $this->buildPostProvider($invalidDecks);
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

    // TODO: Unknown card type should raise an exception
}
