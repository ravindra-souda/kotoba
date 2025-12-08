<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\{Card, Deck};
use App\Exception\CardsNotFoundException;
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
        'title' => '     My first ten animals     ',
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

    private const POST_DEDUP_CARDS_DECK = [
        'title' => 'Catch-all',
    ];

    private const DUMMY = [
        'title' => 'dummy',
    ];

    private const POST_REVERSE_MAPPING_DECKS = [
        'reverse_mapping_beta' => [
            'title' => 'Beta reverse mapping',
            'type' => 'any',
        ], 
        'reverse_mapping_alpha' => [
            'title' => 'Alpha reverse mapping',
            'type' => 'kanji',
        ],
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

    private const POST_INVALID_ASSOCIATIONS_DECK = [
        ...self::POST_COMPLETE_VALID_DECK,
        'title' => 'A deck with wrong cards associations',
    ];

    private const POST_CARD_REMOVAL_DECKS = [
        'card_removal_any' => [
            'title' => 'Post Card Removal Any',
            'description' => 'card_removal_any',
            'type' => 'any',
            'color' => '#FF5050D0',
        ],
        'card_removal_kanji_1' => [
            'title' => 'Post Card Removal Kanji 1',
            'description' => 'card_removal_kanji_1',
            'type' => 'kanji',
            'color' => '#50FF50D0',
        ],
        'card_removal_kanji_2' => [
            'title' => 'Post Card Removal Kanji 2',
            'description' => 'card_removal_kanji_2',
            'type' => 'kanji',
            'color' => '#5050FFD0',
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
        'kanji_card_removal' => [
            'kanji' => '十',
            'meaning' => [
                'en' => ['ten'],
            ],
            'kunyomi' => ['too', 'to', 'so'],
            'onyomi' => ['jū'],
        ],
        'kanji_to_delete' => [
            'kanji' => '百',
            'meaning' => [
                'en' => ['hundred'],
            ],
            'kunyomi' => ['momo'],
            'onyomi' => ['hyaku', 'byaku'],
        ],
        'nouns_to_delete' => [
            'hiragana' => 'ひゃく',
            'kanji' => '百',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['hundred'],
            ],
        ],
        'kanji_reverse_mapping' => [
            'kanji' => '千',
            'meaning' => [
                'en' => ['thousand'],
            ],
            'kunyomi' => ['chi'],
            'onyomi' => ['sen'],
        ],
    ];

    private const CARDS_ASSOCIATIONS = [
        'any' => [
            'nouns_numbers_1', 'kanji_numbers_1', 'nouns_both_1', 
            'nouns_both_2', 'verbs_numbers_1', 'adjectives_numbers_1', 
        ],
        'any_sorted' => [
            'nouns_numbers_1', 'verbs_numbers_1', 'adjectives_numbers_1',
            'nouns_both_1', 'nouns_both_2', 'kanji_numbers_1',
        ],
        'specific' => [
            'nouns_both_2', 'nouns_both_1', 'nouns_animals_2', 
            'nouns_animals_1',
        ],
        'specific_sorted' => [
            'nouns_animals_1', 'nouns_animals_2', 'nouns_both_1', 
            'nouns_both_2',
        ],
        'dedup' => [
            'adjectives_numbers_1', 'nouns_numbers_1', 
            'nouns_both_1', 'nouns_both_1', 'nouns_both_2', 'verbs_numbers_1', 
            'verbs_numbers_1', 'verbs_numbers_1', 'kanji_numbers_1',
        ],
        'card_removal_any' => [
            'nouns_animals_2', 'kanji_to_delete', 'nouns_animals_1',
        ],
        'card_removal_any_sorted' => [
            'nouns_animals_1', 'nouns_animals_2',
        ],
        'card_removal_kanji_1' => [
            'kanji_card_removal', 'kanji_to_delete',
        ],
        'card_removal_kanji_1_sorted' => [
            'kanji_card_removal'
        ],
        'card_removal_kanji_2' => [
            'kanji_to_delete',
        ],
        'card_removal_kanji_2_sorted' => [],
        'reverse_mapping_alpha' => [
            'kanji_reverse_mapping',
        ],
        'reverse_mapping_beta' => [
            'kanji_reverse_mapping', 'kanji_numbers_1', 'verbs_numbers_1'
        ],
    ];

    private static array $decksWithAssociations = [
        'any' => [
            'cards' => [],
        ],
        'any_sorted' => [
            'cards' => [],
        ],
        'specific' => [
            'cards' => [],
        ],
        'specific_sorted' => [
            'cards' => [],
        ],
        'dedup' => [
            'cards' => [],
        ],
        'card_removal_any' => [
            'cards' => [],
        ],
        'card_removal_any_sorted' => [
            'cards' => [],
        ],
        'card_removal_kanji_1' => [
            'cards' => [],
        ],
        'card_removal_kanji_1_sorted' => [
            'cards' => [],
        ],
        'card_removal_kanji_2' => [
            'cards' => [],
        ],
        'card_removal_kanji_2_sorted' => [
            'cards' => [],
        ],
        'reverse_mapping_alpha' => [
            'cards' => [],
        ],
        'reverse_mapping_beta' => [
            'cards' => [],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
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
                'specific_sorted',
                'my-first-ten-animals',
            ],
            'minimal_deck' => [
                self::POST_MINIMAL_VALID_DECK,
                'any',
                self::POST_MINIMAL_VALID_DECK,
                'any_sorted',
                'numbers',
            ],
            'dedup_deck' => [
                self::POST_DEDUP_CARDS_DECK,
                'dedup',
                self::POST_DEDUP_CARDS_DECK,
                'any_sorted',
                'catch-all',
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
        $iris = self::$decksWithAssociations[$expectedCardsKey]['cards'];
        $expected['cards'] = $this->getCardsFromIri($iris);

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
        $this->assertArrayNotHasKey('updatedAt', $content);
        $this->assertMatchesRegularExpression(
            '/\d+-'.$code.'/',
            $content['code']
        );
    }

    public function testDecksAssociationsCardRemoval(): void
    {
        foreach (self::POST_CARD_REMOVAL_DECKS as $key => $payload) {
            $payload['cards'] = self::$decksWithAssociations[$key]['cards'];
            static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );

            $this->assertResponseStatusCodeSame(201);
        }
        
        static::createClient()->request(
            'DELETE',
            self::$cardsToBeRemoved['kanji_to_delete']['iri'],
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?title=post card removal',
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
                self::$cardsToBeRemoved['kanji_to_delete']['content'], 
                $deck['cards']
            );

            $key = $deck['description'].'_sorted';
            $iris = self::$decksWithAssociations[$key]['cards'];
            $expectedCards = $this->getCardsFromIri($iris);

            array_walk(
                $deck['cards'], function(&$card) {
                    unset($card['updatedAt']);
                    return $card;
                }
            );
            $this->assertEquals($deck['cards'], $expectedCards);
        }
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

    public function testDecksPostInvalidAssociations(): void
    {
        $payload = self::POST_INVALID_ASSOCIATIONS_DECK;
        $payload['cards'] = self::$decksWithAssociations['dedup']['cards'];

        $invalidCards = array_filter(
            self::$cardIris,
            fn ($key) => !str_contains($key, 'nouns_'),
            ARRAY_FILTER_USE_KEY
        );
        $invalidIris = array_intersect(
            array_unique($payload['cards']),
            $invalidCards
        );

        $message = Deck::formatMsg(
            'cards: '.Deck::VALIDATION_ERR_CARDS_ASSOCIATIONS, 
            Deck::sortByIri($invalidIris)
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testDecksAssociationsUnknownCard(): void
    {
        static::createClient()->request(
            'DELETE',
            self::$cardsToBeRemoved['nouns_to_delete']['iri'],
        );
        $this->assertResponseStatusCodeSame(204);

        $invalidIris = [
            self::$cardsToBeRemoved['nouns_to_delete']['iri'],
            '/api/cards/dummy/99-dame',
            '/api/cards/adjectives',
            '/api/dummy/kanji/1-two',
        ];

        $payload = [
            ...self::POST_MINIMAL_VALID_DECK,
            'title' => 'post deck unknown card',
            'cards' => [
                ...self::$decksWithAssociations['any']['cards'],
                ...$invalidIris,
            ]
        ];

        $message = Deck::formatMsg(
            CardsNotFoundException::MESSAGE_TEMPLATE, 
            $invalidIris
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testReverseMappingCard(): array
    {
        $deckIris = [];

        // reverse mapping when posting new decks
        foreach(self::POST_REVERSE_MAPPING_DECKS as $key => $payload) {
            $payload['cards'] = self::$decksWithAssociations[$key]['cards'];

            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );

            $this->assertResponseStatusCodeSame(201);
            $deck = json_decode($response->getContent(), true);
            $deckIris[] = $deck['@id'];
        }

        $response = static::createClient()->request(
            'GET',
            self::$reverseMappingCardIri,
        );
        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($response->getContent(), true);
        $contentDeckIris = 
            array_map(fn($deck) => $deck['@id'], $content['decks']);

        $this->assertSame($contentDeckIris, Deck::sortByIri($deckIris));

        // reverse mapping when deleting a deck
        $deckToDelete = $deckIris[0];
        static::createClient()->request(
            'DELETE',
            $deckToDelete,
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'GET',
            self::$reverseMappingCardIri,
        );
        $this->assertResponseStatusCodeSame(200);

        $card = json_decode($response->getContent(), true);
        $contentDeckIris = 
            array_map(fn($deck) => $deck['@id'], $card['decks']);
        
        $this->assertNotContains($deckToDelete, $contentDeckIris);
        unset($deckIris[0]);
        $this->assertSame($contentDeckIris, Deck::sortByIri($deckIris));

        return [$deck, $card];
    }

    /**
     * @depends testReverseMappingCard
     */
    public function testNoCircularReferences(array $fixtures): void
    {
        [$deck, $card] = $fixtures;

        $this->assertArrayHasKey('cards', $deck);
        foreach ($deck['cards'] as $cardInsideDeck) {
            $this->assertArrayNotHasKey('decks', $cardInsideDeck);
        }

        $this->assertArrayHasKey('decks', $card);
        foreach ($card['decks'] as $deckInsideCard) {
            $this->assertArrayNotHasKey('cards', $deckInsideCard);
        }
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
