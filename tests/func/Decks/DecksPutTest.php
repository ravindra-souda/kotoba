<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Deck;
use App\Exception\CardsNotFoundException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 *
 * @phpstan-import-type DeckType from Types
 * @phpstan-import-type DeckExpectedType from Types
 * @phpstan-import-type CardExpectedType from Types
 */
class DecksPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;
    use Trait\CardAssociationTrait;

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
        'cards' => [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => 'put edit cards',
        ],
    ];

    private const PUT_REVERSE_MAPPING_DECKS = [
        'reverse_mapping_epsilon' => [
            'title' => 'Epsilon reverse mapping',
            'type' => 'nouns',
        ],
        'reverse_mapping_delta' => [
            'title' => 'Delta reverse mapping',
            'type' => 'any',
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
            'title' => '(Put Associations): Welcome to the urban jungle',
            'description' => 'Surviving guide to this new city',
            'type' => 'any',
            'color' => '#2f2492e0',
        ],
        'association_specific' => [
            'title' => '(Put Associations): Pets',
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
        'color' => [
            'fixture' => 'color',
            'payload' => [
                'color' => '#G1F2F3F4',
            ],
            'message' => 'color: '.Deck::VALIDATION_ERR_COLOR,
        ],
    ];

    private const PUT_INVALID_ASSOCIATIONS_DECK = [
        ...self::PUT_VALID_DECKS['association_specific'],
        'title' => 'Things went wrong with cards associations...',
    ];

    private const PUT_CARD_REMOVAL_DECKS = [
        'card_removal_any' => [
            'title' => 'Put Card Removal Any',
            'description' => 'card_removal_any',
            'type' => 'any',
            'color' => '#FF5050D0',
        ],
        'card_removal_kana_1' => [
            'title' => 'Put Card Removal Kana 1',
            'description' => 'card_removal_kana_1',
            'type' => 'kana',
            'color' => '#50FF50D0',
        ],
        'card_removal_kana_2' => [
            'title' => 'Put Card Removal Kana 2',
            'description' => 'card_removal_kana_2',
            'type' => 'kana',
            'color' => '#5050FFD0',
        ],
    ];

    private const CARDS_ATTACHED_TO_DECKS = [
        'nouns_city_1' => [
            'katakana' => 'コンビニ',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['convenience store'],
                'fr' => ['commerce de proximité, supérette'],
            ],
        ],
        'nouns_city_2' => [
            'hiragana' => 'まち',
            'kanji' => '町',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['town, block, neighbourhood'],
                'fr' => ['ville, quartier, voisinnage'],
            ],
        ],
        'nouns_pets_1' => [
            'katakana' => 'ハムスター',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['hamster'],
            ],
        ],
        'nouns_pets_2' => [
            'hiragana' => 'うさぎ',
            'kanji' => '兎',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['rabbit'],
            ],
        ],
        'nouns_both_1' => [
            'hiragana' => 'こいぬ',
            'kanji' => '子犬',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['puppy'],
            ],
        ],
        'nouns_both_2' => [
            'hiragana' => 'ねこ',
            'kanji' => '猫',
            'jlpt' => 5,
            'meaning' => [
                'en' => ['cat'],
            ],
        ],
        'verbs_city_1' => [
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
        'verbs_city_2' => [
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
        'adjectives_city_1' => [
            'hiragana' => 'にぎやか',
            'kanji' => '賑やか',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => ['bustling, busy, crowded, lively, prosperous'],
            ],
        ],
        'kanji_5' => [
            'kanji' => '五',
            'meaning' => [
                'en' => ['five'],
            ],
            'kunyomi' => ['itsu', 'itsutsu'],
            'onyomi' => ['go'],
        ],
        'kanji_6' => [
            'kanji' => '六',
            'meaning' => [
                'en' => ['six'],
            ],
            'kunyomi' => ['mu', 'mutsu', 'むっつ', 'mui'],
            'onyomi' => ['roku', 'リク'],
        ],
        'kanji_8' => [
            'kanji' => '八',
            'meaning' => [
                'en' => ['eight'],
            ],
            'kunyomi' => ['や', 'やつ', 'やっつ', 'よう'],
            'onyomi' => ['ハチ', 'ハツ'],
        ],
        'kanji_10' => [
            'kanji' => '十',
            'meaning' => [
                'en' => ['ten'],
            ],
            'kunyomi' => ['とお', 'と', 'そ'],
            'onyomi' => ['ジュウ', 'ジッ', 'ジュッ'],
        ],
        'kanji_1000' => [
            'kanji' => '千',
            'meaning' => [
                'en' => ['thousand'],
            ],
            'kunyomi' => ['chi'],
            'onyomi' => ['sen'],
        ],
        'kana_card_removal' => [
            'hiragana' => 'あ',
        ],
        'kana_to_delete' => [
            'katakana' => 'ア',
        ],
        'verbs_to_delete' => [
            'hiragana' => 'けす',
            'kanji' => '消す',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => ['to delete'],
            ],
            'inflections' => [
                'dictionary' => '消す',
            ],
        ],
        'nouns_reverse_mapping' => [
            'hiragana' => 'せん',
            'kanji' => '千',
            'meaning' => [
                'en' => ['thousand'],
            ],
        ],
    ];

    private const CARDS_ASSOCIATIONS = [
        'any' => [
            'nouns_city_2', 'nouns_city_1', 'nouns_both_2', 'nouns_both_1',
            'verbs_city_2', 'adjectives_city_1', 'verbs_city_1',
        ],
        'any_sorted' => [
            'verbs_city_1', 'verbs_city_2', 'nouns_both_1', 'nouns_city_1',
            'nouns_city_2', 'nouns_both_2', 'adjectives_city_1',
        ],
        'specific' => [
            'nouns_pets_2', 'nouns_pets_1', 'nouns_both_2', 'nouns_both_1',
        ],
        'specific_sorted' => [
            'nouns_pets_1', 'nouns_both_1', 'nouns_both_2', 'nouns_pets_2',
        ],
        'dedup' => [
            'nouns_city_2', 'nouns_city_1', 'nouns_both_2', 'nouns_both_1',
            'nouns_pets_2', 'nouns_pets_1', 'nouns_both_2', 'nouns_both_1',
            'verbs_city_1', 'adjectives_city_1',
            'nouns_pets_2', 'nouns_both_1', 'adjectives_city_1', 'verbs_city_1',
            'verbs_city_2',
        ],
        'dedup_sorted' => [
            'verbs_city_1', 'nouns_pets_1', 'verbs_city_2', 'nouns_both_1',
            'nouns_city_1', 'nouns_city_2', 'nouns_both_2', 'adjectives_city_1',
            'nouns_pets_2',
        ],
        'cards_fixture' => [
            'kanji_5', 'kanji_6', 'kanji_10',
        ],
        'cards_remove_payload' => [
            'kanji_10', 'kanji_5',
        ],
        'cards_remove_sorted' => [
            'kanji_5', 'kanji_10',
        ],
        'cards_clear' => [],
        'cards_edit' => [
            'kanji_1000', 'kanji_8', 'kanji_10',
        ],
        'cards_edit_sorted' => [
            'kanji_8', 'kanji_10', 'kanji_1000',
        ],
        'card_removal_any' => [
            'nouns_both_2', 'kana_to_delete', 'nouns_both_1',
        ],
        'card_removal_any_sorted' => [
            'nouns_both_1', 'nouns_both_2',
        ],
        'card_removal_kana_1' => [
            'kana_card_removal', 'kana_to_delete',
        ],
        'card_removal_kana_1_sorted' => [
            'kana_card_removal',
        ],
        'card_removal_kana_2' => [
            'kana_to_delete',
        ],
        'card_removal_kana_2_sorted' => [],
        'reverse_mapping_delta' => [
            'verbs_city_1', 'verbs_city_2', 'nouns_both_1', 'nouns_city_1',
            'nouns_city_2', 'nouns_both_2', 'adjectives_city_1',
            'nouns_reverse_mapping',
        ],
        'reverse_mapping_epsilon' => [
            'nouns_pets_1', 'nouns_reverse_mapping',
        ],
    ];

    /** @var array<string,array{cards:list<string>}> */
    private static array $decksWithAssociations = [
        'any' => [
            ...self::PUT_VALID_DECKS['association_any'],
            'cards' => [],
        ],
        'any_sorted' => [
            'cards' => [],
        ],
        'specific' => [
            ...self::PUT_VALID_DECKS['association_specific'],
            'cards' => [],
        ],
        'specific_sorted' => [
            'cards' => [],
        ],
        'dedup' => [
            ...self::PUT_VALID_DECKS['association_any'],
            'title' => 'association dedup',
            'cards' => [],
        ],
        'dedup_sorted' => [
            'cards' => [],
        ],
        'cards_fixture' => [
            'cards' => [],
        ],
        'cards_remove_payload' => [
            'cards' => [],
        ],
        'cards_remove_sorted' => [
            'cards' => [],
        ],
        'cards_clear' => [
            'cards' => [],
        ],
        'cards_edit' => [
            'cards' => [],
        ],
        'cards_edit_sorted' => [
            'cards' => [],
        ],
        'card_removal_any' => [
            'cards' => [],
        ],
        'card_removal_any_sorted' => [
            'cards' => [],
        ],
        'card_removal_kana_1' => [
            'cards' => [],
        ],
        'card_removal_kana_1_sorted' => [
            'cards' => [],
        ],
        'card_removal_kana_2' => [
            'cards' => [],
        ],
        'card_removal_kana_2_sorted' => [
            'cards' => [],
        ],
        'reverse_mapping_delta' => [
            'cards' => [],
        ],
        'reverse_mapping_epsilon' => [
            'cards' => [],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        self::initializeCardsBeforeAllTests();
    }

    /**
     * @return array<string,array<null|DeckType|string>>
     */
    public function validDeckProvider(): array
    {
        return [
            'title' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 1',
                ],
                null,
                self::PUT_VALID_DECKS['title'],
                null,
                [
                    ...self::PUT_VALID_DECKS['title'],
                    'title' => 'Basic vocabulary',
                ],
                null,
                'basic-vocabulary',
            ],
            'description' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 2',
                ],
                null,
                self::PUT_VALID_DECKS['description'],
                null,
                [
                    ...self::PUT_VALID_DECKS['description'],
                    'description' => 'Words you need to know before travel',
                ],
                null,
                'basic-vocab-2',
            ],
            'type' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 3',
                ],
                null,
                self::PUT_VALID_DECKS['type'],
                null,
                self::PUT_VALID_DECKS['type'],
                null,
                'basic-vocab-3',
            ],
            'association_any' => [
                self::PUT_VALID_DECKS['association_any'],
                null,
                self::PUT_VALID_DECKS['association_any'],
                'any',
                self::PUT_VALID_DECKS['association_any'],
                'any_sorted',
                'put-associations-welcome-to-the-urban-jungle',
            ],
            'association_specific' => [
                self::PUT_VALID_DECKS['association_specific'],
                null,
                self::PUT_VALID_DECKS['association_specific'],
                'specific',
                self::PUT_VALID_DECKS['association_specific'],
                'specific_sorted',
                'put-associations-pets',
            ],
            'association_dedup' => [
                [
                    ...self::PUT_VALID_DECKS['association_any'],
                    'title' => '(Put Associations): dedup',
                ],
                null,
                [
                    ...self::PUT_VALID_DECKS['association_any'],
                    'title' => 'association dedup',
                ],
                'dedup',
                [
                    ...self::PUT_VALID_DECKS['association_any'],
                    'title' => 'association dedup',
                ],
                'dedup_sorted',
                'association-dedup',
            ],
            'color' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'Basic vocab 4',
                ],
                null,
                self::PUT_VALID_DECKS['color'],
                null,
                self::PUT_VALID_DECKS['color'],
                null,
                'basic-vocab-4',
            ],
            'cards_remove' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (remove)',
                ],
                'cards_fixture',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (remove)',
                ],
                'cards_remove_payload',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (remove)',
                ],
                'cards_remove_sorted',
                'put-edit-cards-remove',
            ],
            'cards_clear' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (clear)',
                ],
                'cards_fixture',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (clear)',
                ],
                'cards_clear',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (clear)',
                ],
                'cards_clear',
                'put-edit-cards-clear',
            ],
            'cards_edit' => [
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (edit)',
                ],
                'cards_fixture',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (edit)',
                ],
                'cards_edit',
                [
                    ...self::PUT_COMPLETE_VALID_DECK,
                    'title' => 'put edit cards (edit)',
                ],
                'cards_edit_sorted',
                'put-edit-cards-edit',
            ],
        ];
    }

    /**
     * @dataProvider validDeckProvider
     *
     * @param DeckType $fixture
     * @param DeckType $payload
     * @param DeckType $expected
     */
    public function testDecksPutValid(
        array $fixture,
        ?string $fixtureCardsKey,
        array $payload,
        ?string $payloadCardsKey,
        array $expected,
        ?string $expectedCardsKey,
        string $code
    ): void {
        if (null !== $fixtureCardsKey) {
            $fixture['cards'] =
                self::$decksWithAssociations[$fixtureCardsKey]['cards'];
        }
        if (null !== $payloadCardsKey) {
            $payload['cards'] =
                self::$decksWithAssociations[$payloadCardsKey]['cards'];
        }
        if (null !== $expectedCardsKey) {
            $iris = self::$decksWithAssociations[$expectedCardsKey]['cards'];
            $expected['cards'] = $this->getCardsFromIri($iris);
        }

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

    public function testDecksAssociationsCardRemoval(): void
    {
        foreach (self::PUT_CARD_REMOVAL_DECKS as $key => $payload) {
            // setting up fixture
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );
            $this->assertResponseStatusCodeSame(201);
            $_id = json_decode($response->getContent(), true)['@id'];

            // editing by adding cards
            $payload['@id'] = $_id;
            $payload['cards'] = self::$decksWithAssociations[$key]['cards'];
            static::createClient()->request(
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
        }

        static::createClient()->request(
            'DELETE',
            self::$cardsToBeRemoved['kana_to_delete']['iri'],
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?title=put card removal',
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
                self::$cardsToBeRemoved['kana_to_delete']['content'],
                $deck['cards']
            );

            $key = $deck['description'].'_sorted';
            $iris = self::$decksWithAssociations[$key]['cards'];
            $expectedCards = $this->getCardsFromIri($iris);

            array_walk(
                $deck['cards'],
                function (&$card) {
                    unset($card['updatedAt']);

                    return $card;
                }
            );
            $this->assertEquals($deck['cards'], $expectedCards);
        }
    }

    /**
     * @return array<string,array<DeckType|string>>
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
     * @param DeckType $fixture
     * @param DeckType $payload
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

        if (isset($payload['title'])
            && self::UNIQUE_TITLE === $payload['title']) {
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

    public function testDecksPutInvalidAssociations(): void
    {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => self::PUT_INVALID_ASSOCIATIONS_DECK]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        $payload = self::PUT_INVALID_ASSOCIATIONS_DECK;
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

    public function testDecksAssociationsUnknownCard(): void
    {
        // setting up fixture
        $title = "Let's send a bunch of invalid IRI and see what happens";
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => [
                ...self::PUT_COMPLETE_VALID_DECK,
                'title' => $title,
            ],
            ],
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        static::createClient()->request(
            'DELETE',
            self::$cardsToBeRemoved['verbs_to_delete']['iri'],
        );
        $this->assertResponseStatusCodeSame(204);

        // actual testing
        $validIri = self::$decksWithAssociations['any']['cards'][0];
        [$root, $api, $cards, $type, $code] = explode('/', $validIri);
        $invalidIris = [
            self::$cardsToBeRemoved['verbs_to_delete']['iri'],
            ltrim($validIri, '/'),
            '/'.ltrim($validIri, '/api'),
            '/'.ltrim($validIri, '/api/cards'),
            $code,
            "/api/{$type}/{$code}",
            '/api/cards/dummy/'.$code,
            '/api/cards/'.$type,
            "/api/dummy/{$type}/{$code}",
        ];

        $payload = [
            ...self::PUT_COMPLETE_VALID_DECK,
            'title' => $title,
            'cards' => [
                ...self::$decksWithAssociations['any']['cards'],
                ...$invalidIris,
            ],
        ];

        $message = Deck::formatMsg(
            CardsNotFoundException::MESSAGE_TEMPLATE,
            $invalidIris
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

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

    /**
     * @return array{0:DeckExpectedType,1:CardExpectedType}
     */
    public function testReverseMappingCard(): array
    {
        $deckIris = [];

        // reverse mapping when editing decks
        foreach (self::PUT_REVERSE_MAPPING_DECKS as $key => $payload) {
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );
            $this->assertResponseStatusCodeSame(201);
            $_id = json_decode($response->getContent(), true)['@id'];

            $payload['cards'] = self::$decksWithAssociations[$key]['cards'];

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
            $deckIris[] = $_id;
            $deck = json_decode($response->getContent(), true);
        }

        $response = static::createClient()->request(
            'GET',
            self::$reverseMappingCardIri,
        );
        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($response->getContent(), true);
        $contentDeckIris =
            array_map(fn ($deck) => $deck['@id'], $content['decks']);

        $this->assertSame($contentDeckIris, Deck::sortByIri($deckIris));

        // reverse mapping when deleting a deck
        $deckToDelete = $deckIris[1];
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
            array_map(fn ($deck) => $deck['@id'], $card['decks']);

        $this->assertNotContains($deckToDelete, $contentDeckIris);
        unset($deckIris[1]);
        $this->assertSame($contentDeckIris, Deck::sortByIri($deckIris));

        return [$deck, $card];
    }

    /**
     * @depends testReverseMappingCard
     *
     * @param array{0:DeckExpectedType,1:CardExpectedType} $fixtures
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
