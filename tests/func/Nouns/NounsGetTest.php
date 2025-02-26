<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Noun;

/**
 * @internal
 *
 * @coversNothing
 */
class NounsGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'hiragana' => [
            'hiragana' => 'とら',
            'meaning' => [
                'en' => ['tiger'],
            ],
        ],
        'hiragana_2' => [
            'hiragana' => 'うま',
            'meaning' => [
                'en' => ['horse'],
            ],
        ],
        'hiragana_3' => [
            'hiragana' => 'うし',
            'meaning' => [
                'en' => ['cow', 'beef'],
            ],
        ],
        'kanji' => [
            'hiragana' => 'おおかみ',
            'kanji' => '狼',
            'meaning' => [
                'en' => ['wolf'],
                'fr' => ['loup'],
            ],
        ],
        'kanji_2' => [
            'hiragana' => 'がめ',
            'kanji' => '亀',
            'meaning' => [
                'en' => ['turtle'],
            ],
        ],
        'kanji_3' => [
            'hiragana' => 'うみがめ',
            'kanji' => '海亀',
            'meaning' => [
                'en' => ['sea turtle'],
            ],
        ],
        'katakana' => [
            'katakana' => 'ライオン',
            'meaning' => [
                'en' => ['lion'],
            ],
        ],
        'katakana_2' => [
            'katakana' => 'ウナギ',
            'meaning' => [
                'en' => ['eel'],
            ],
        ],
        'katakana_3' => [
            'katakana' => 'ウニ',
            'meaning' => [
                'en' => ['sea urchin'],
            ],
        ],
        'meaning' => [
            'hiragana' => 'きつね',
            'kanji' => '狐',
            'meaning' => [
                'en' => ['fox', 'light brown; golden brown'],
            ],
        ],
        'romaji' => [
            'hiragana' => 'ひょう',
            'kanji' => '豹',
            'meaning' => [
                'en' => ['leopard'],
            ],
            'romaji' => 'hyou',
        ],
    ];
    private const GET_SORT_FIXTURES = [
        'romaji_asc' => [
            [
                'hiragana' => 'らく',
                'group' => 'na',
                'meaning' => [
                    'en' => ['comfort; ease; relief']
                ],
            ],
            [
                'hiragana' => 'らっかんてき',
                'group' => 'na',
                'meaning' => [
                    'en' => ['optimistic; hopeful']
                ],
            ],
            [
                'katakana' => 'ラッキー',
                'group' => 'na',
                'meaning' => [
                    'en' => ['lucky']
                ],
            ],
        ],
        'romaji_desc' => [
            [
                'hiragana' => 'しずか',
                'group' => 'na',
                'meaning' => [
                    'en' => ['quiet; silent']
                ],
            ],
            [
                'hiragana' => 'しろい',
                'group' => 'i',
                'meaning' => [
                    'en' => ['white']
                ],
            ],
            [
                'hiragana' => 'しょうじき',
                'group' => 'na',
                'meaning' => [
                    'en' => ['honest; frank; candid; straightforward']
                ],
            ],
        ],
        'search_and_sort' => [
            [
                'hiragana' => 'まるい',
                'kanji' => '丸い',
                'group' => 'i',
                'meaning' => [
                    'en' => ['round; circular; spherical']
                ],
            ],
            [
                'hiragana' => 'まずい',
                'group' => 'i',
                'meaning' => [
                    'en' => [
                        'bad(-tasting); awful; terrible',
                        'poor; unskillful; search-me'
                    ],
                ],
            ],
            [
                'hiragana' => 'まんぞく',
                'kanji' => '満足',
                'group' => 'na',
                'meaning' => [
                    'en' => ['sufficient; satisfactory; enough; adequate']
                ],
            ],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        $fixtures = array_merge_recursive(
            array_values(self::GET_SEARCH_FIXTURES),
            ...array_values(self::GET_SORT_FIXTURES),
        );
        array_walk($fixtures, fn(&$fixture) => 
            $fixture['romaji'] = 'pagination'.Noun::toRomaji($fixture['hiragana'] ?? $fixture['katakana'])
        );
        foreach ($fixtures as $payload) {
            static::createClient()->request(
                'POST',
                '/api/cards/nouns',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Adjective::class);
        }
    }

    private function getItemsPerPage(): int 
    {
        return (int) $_ENV["ITEMS_PER_PAGE"];
    }

    /**
     * @return array<array<array<string>>>
     */
    public function searchNounsProvider(): array
    {
        return [
            'hiragana' => [
                'url' => '?hiragana=と&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana']
                ],
            ],
            'hiragana_start' => [
                'url' => '?hiragana=う&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                    self::GET_SEARCH_FIXTURES['hiragana_3'],
                ],
            ],
            'kanji' => [
                'url' => '?kanji=狼&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji']
                ],
            ],
            'kanji_partial' => [
                'url' => '?kanji=亀&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                    self::GET_SEARCH_FIXTURES['kanji_3'],
                ],
            ],
            'katakana' => [
                'url' => '?katakana=ライ&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'katakana_start' => [
                'url' => '?katakana=ウ&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana_3'],
                ],
            ],
            'meaning' => [
                'url' => '?meaning[lang]=en&meaning[search]=sea&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_3'],
                    self::GET_SEARCH_FIXTURES['katakana_3'],
                ],
            ],
            'meaning_insensitive' => [
                'url' => '?meaning[lang]=en&meaning[search]=TurTLe&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_3'],
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                ],
            ],
            'meaning_lang' => [
                'url' => '?meaning[lang]=fr&meaning[search]=loup&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji']
                ],
            ],
            'meaning_lang_unknown' => [
                'url' => '?meaning[lang]=dummy&meaning[search]=tiger&romaji=pagination',
                'expected' => [],
            ],
            'meaning_lang_missing' => [
                'url' => '?meaning[search]=tiger&romaji=paginationu',
                'expected' => self::GET_SORT_FIXTURES['search_and_sort'],
            ],
            'meaning_search_missing' => [
                'url' => '?meaning[lang]=en&romaji=paginationu',
                'expected' => self::GET_SORT_FIXTURES['search_and_sort'],
            ],
            'romaji' => [
                'url' => '?romaji=paginationhY',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji']
                ],
            ],
            'romaji_start' => [
                'url' => '?romaji=paginationU&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['search_and_sort'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider searchNounsProvider
     *
     * @param array<string> $expected
     */
    public function testNounsGetSearch(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/nouns'.$url,
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], count($expected));
        $this->assertArraySubset(
            array_slice($expected, 0, $this->getItemsPerPage()),
            $content['hydra:member']
        );
        $this->assertMatchesResourceCollectionJsonSchema(Noun::class);
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortAdjectiveProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=paginationra&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_asc'][1],
                    self::GET_SORT_FIXTURES['romaji_asc'][2],
                    self::GET_SORT_FIXTURES['romaji_asc'][0],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=paginationsh&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_desc'][2],
                    self::GET_SORT_FIXTURES['romaji_desc'][0],
                    self::GET_SORT_FIXTURES['romaji_desc'][1],
                ],
            ],
            'search_and_order' => [
                'url' => '?hiragana=ま&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['search_and_sort'][1],
                    self::GET_SORT_FIXTURES['search_and_sort'][0],
                    self::GET_SORT_FIXTURES['search_and_sort'][2],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortAdjectiveProvider
     *
     * @param array<array<string>> $expected
     */
    public function testAdjectivesGetSort(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Adjective::class);
    }

    public function testAdjectivesGetOneAdjective(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/cards/adjectives?kanji='.
            self::GET_SORT_FIXTURES['search_and_sort'][0]['kanji']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['search_and_sort'][0],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Adjective::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testAdjectivesGetOneAdjective
     */
    public function testAdjectivesGetOneAdjectiveByCode(string $code): void
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
            self::GET_SORT_FIXTURES['search_and_sort'][0]
        );

        $this->assertMatchesResourceItemJsonSchema(Adjective::class);
    }

    public function testAdjectivesGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/adjectives/dummy',
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<array<string>>
     */
    public function emptyCollectionProvider(): array
    {
        return [
            'field' => ['?romaji=dummy'],
        ];
    }

    /**
     * @dataProvider emptyCollectionProvider
     */
    public function testAdjectivesGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/adjectives'.$url,
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

    public function testAdjectivesGetPagination(): void
    {
        $totalItems = count(array_merge_recursive(
                array_values(self::GET_SEARCH_FIXTURES),
                ...array_values(self::GET_SORT_FIXTURES),
            )
        );
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);
        
        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives?romaji=pagination',
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
                'hydra:first' => '/api/cards/adjectives?romaji=pagination&page=1',
                'hydra:last' => '/api/cards/adjectives?romaji=pagination&page='.$lastPage,
                'hydra:next' => '/api/cards/adjectives?romaji=pagination&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives?romaji=pagination&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['romaji_desc'][1],
            $content['hydra:member'][2]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives?romaji=pagination&pagination=false',
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
            '/api/cards/adjectives?romaji=pagination&itemsPerPage=1',
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $totalItems,
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
    }
}
