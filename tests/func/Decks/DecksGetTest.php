<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Deck;

/**
 * @internal
 *
 * @coversNothing
 */
class DecksGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'title' => [
            'title' => 'search me',
            'description' => '(get pagination)',
            'type' => 'any',
        ],
        'description' => [
            'title' => 'not me',
            'description' => 'search me (get pagination)',
            'type' => 'any',
        ],
        'type' => [
            'title' => 'search my nouns deck please',
            'description' => '(get pagination)',
            'type' => 'nouns',
        ],
    ];
    private const GET_SORT_FIXTURES = [
        'title_asc' => [
            [
                'title' => 'title_sort alpha',
                'description' => '(get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'title_sort beta',
                'description' => '(get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'title_sort gamma',
                'description' => '(get pagination)',
                'type' => 'any',
            ],
        ],
        'description_desc' => [
            [
                'title' => 'desc 1',
                'description' => 'alpha 1 (get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'desc 2',
                'description' => 'alpha 2 (get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'desc 3',
                'description' => 'alpha 3 (get pagination)',
                'type' => 'any',
            ],
        ],
        'description_asc, title_desc' => [
            [
                'title' => 'two fields 1',
                'description' => 'alpha (get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'two fields 2',
                'description' => 'beta (get pagination)',
                'type' => 'any',
            ],
            [
                'title' => 'two fields 3',
                'description' => 'alpha (get pagination)',
                'type' => 'any',
            ],
        ],
        'search and sort' => [
            [
                'title' => 'hiragana K-column',
                'description' => 'initiation to hiragana (get pagination)',
                'type' => 'kana',
            ],
            [
                'title' => 'hiragana S-column',
                'description' => 'initiation to hiragana (get pagination)',
                'type' => 'kana',
            ],
            [
                'title' => 'hiragana T-column',
                'description' => 'initiation to hiragana (get pagination)',
                'type' => 'kana',
            ],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        $fixtures = array_merge_recursive(
            array_values(self::GET_SEARCH_FIXTURES),
            ...array_values(self::GET_SORT_FIXTURES),
        );
        foreach ($fixtures as $payload) {
            static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Deck::class);
        }
    }

    /**
     * @return array<array<array<string>>>
     */
    public function searchDeckProvider(): array
    {
        return [
            'title' => [
                'url' => '?title=rch me',
                'expected' => self::GET_SEARCH_FIXTURES['title'],
            ],
            'description' => [
                'url' => '?description=rCh Me',
                'expected' => self::GET_SEARCH_FIXTURES['description'],
            ],
            'type' => [
                'url' => '?type=NouNS',
                'expected' => self::GET_SEARCH_FIXTURES['type'],
            ],
        ];
    }

    /**
     * @dataProvider searchDeckProvider
     *
     * @param array<string> $expected
     */
    public function testDecksGetSearch(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/decks'.$url,
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset($expected, $content['hydra:member'][0]);
        $this->assertMatchesResourceCollectionJsonSchema(Deck::class);
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortDeckProvider(): array
    {
        return [
            'order_asc' => [
                'url' => '?title=title_sort&order[title]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['title_asc'][0],
                    self::GET_SORT_FIXTURES['title_asc'][1],
                    self::GET_SORT_FIXTURES['title_asc'][2],
                ],
            ],
            'order_desc' => [
                'url' => '?title=desc&order[description]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['description_desc'][2],
                    self::GET_SORT_FIXTURES['description_desc'][1],
                    self::GET_SORT_FIXTURES['description_desc'][0],
                ],
            ],
            'order_asc_then_desc' => [
                'url' => '?title=two fields&order[description]=asc'.
                    '&order[title]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['description_asc, title_desc'][2],
                    self::GET_SORT_FIXTURES['description_asc, title_desc'][0],
                    self::GET_SORT_FIXTURES['description_asc, title_desc'][1],
                ],
            ],
            'search_and_order' => [
                'url' => '?title=hiRAg&order[description]=asc'.
                    '&order[title]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['search and sort'][2],
                    self::GET_SORT_FIXTURES['search and sort'][1],
                    self::GET_SORT_FIXTURES['search and sort'][0],
                ],
            ],
            'enum_and_order' => [
                'url' => '?type[]=nouNs&type[]=kaNa&order[title]=desc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['type'],
                    self::GET_SORT_FIXTURES['search and sort'][2],
                    self::GET_SORT_FIXTURES['search and sort'][1],
                    self::GET_SORT_FIXTURES['search and sort'][0],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortDeckProvider
     *
     * @param array<array<string>> $expected
     */
    public function testDecksGetSort(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/decks'.$url,
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], count($expected));
        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertArraySubset(
                $expected[$i],
                $content['hydra:member'][$i]
            );
        }
        $this->assertMatchesResourceCollectionJsonSchema(Deck::class);
    }

    public function testDecksGetOneDeck(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/decks?title='.
            self::GET_SORT_FIXTURES['search and sort'][1]['title']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['search and sort'][1],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Deck::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testDecksGetOneDeck
     */
    public function testDecksGetOneDeckByCode(string $code): void
    {
        $response = static::createClient()->request(
            'GET',
            $code
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('hydra:totalItems', $content);
        $this->assertJsonContains(
            self::GET_SORT_FIXTURES['search and sort'][1]
        );

        $this->assertMatchesResourceItemJsonSchema(Deck::class);
    }

    public function testDecksGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/decks/dummy',
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<array<string>>
     */
    public function emptyCollectionProvider(): array
    {
        return [
            'field' => ['?title=dummy'], 'enum' => ['?type=kan'],
        ];
    }

    /**
     * @dataProvider emptyCollectionProvider
     */
    public function testDecksGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/decks'.$url,
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 0,
        ]);
    }

    public function testDecksGetPagination(): void
    {
        $totalItems = count(array_merge_recursive(
            array_values(self::GET_SEARCH_FIXTURES),
            ...array_values(self::GET_SORT_FIXTURES),
        ));
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?description=pagination',
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $totalItems,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/decks?description=pagination&page=1',
                'hydra:last' => '/api/decks?description=pagination&page='
                    .$lastPage,
                'hydra:next' => '/api/decks?description=pagination&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?description=pagination&order[title]=desc&page=3',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['description_desc'][2],
            $content['hydra:member'][2]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/decks?description=pagination&pagination=false',
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $totalItems,
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/decks?description=pagination&itemsPerPage=1',
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $totalItems,
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
    }

    private function getItemsPerPage(): int
    {
        return (int) $_ENV['ITEMS_PER_PAGE'];
    }
}
