<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Adjective;

/**
 * @internal
 *
 * @coversNothing
 */
class AdjectivesGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'hiragana' => [
            'hiragana' => 'たのしい',
            'group' => 'i',
            'meaning' => [
                'en' => ['fun'],
            ],
        ],
        'hiragana_2' => [
            'hiragana' => 'たいくつ',
            'group' => 'na',
            'meaning' => [
                'en' => ['tedious; boring'],
            ],
        ],
        'kanji' => [
            'hiragana' => 'おとなしい',
            'kanji' => '大人しい',
            'group' => 'i',
            'meaning' => [
                'en' => ['gentle; quiet'],
            ],
        ],
        'kanji_2' => [
            'hiragana' => 'じんこうてき',
            'kanji' => '人工的',
            'group' => 'na',
            'meaning' => [
                'en' => ['artificial; unnatural; search-me'],
            ],
        ],
        'katakana' => [
            'katakana' => 'ユニーク',
            'group' => 'na',
            'meaning' => [
                'en' => ['unique'],
            ],
        ],
        'katakana_2' => [
            'katakana' => 'ユニバーサル',
            'group' => 'na',
            'meaning' => [
                'en' => ['universal; search-me'],
            ],
        ],
        'meaning' => [
            'hiragana' => 'かんたん',
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'simple; easy; uncomplicated; loin',
                    'brief; quick; light',
                ],
            ],
        ],
        'romaji' => [
            'hiragana' => 'とおい',
            'romaji' => 'paginationtōi',
            'group' => 'i',
            'meaning' => [
                'en' => ['far'],
                'fr' => ['loin'],
            ],
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
            $fixture['romaji'] = 'pagination'.Adjective::toRomaji($fixture['hiragana'] ?? $fixture['katakana'])
        );
        foreach ($fixtures as $payload) {
            static::createClient()->request(
                'POST',
                '/api/cards/adjectives',
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
    public function searchAdjectiveProvider(): array
    {
        return [
            'group_i' => [
                'url' => '?group=i&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['search_and_sort'][0],
                    self::GET_SORT_FIXTURES['search_and_sort'][1],
                    self::GET_SEARCH_FIXTURES['kanji'],
                    self::GET_SORT_FIXTURES['romaji_desc'][1],
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
            'group_na' => [
                'url' => '?group=na&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                    self::GET_SEARCH_FIXTURES['meaning'],
                    self::GET_SORT_FIXTURES['search_and_sort'][2],
                    self::GET_SORT_FIXTURES['romaji_asc'][1],
                    self::GET_SORT_FIXTURES['romaji_asc'][2],
                    self::GET_SORT_FIXTURES['romaji_asc'][0],
                    self::GET_SORT_FIXTURES['romaji_desc'][0],
                    self::GET_SORT_FIXTURES['romaji_desc'][2],
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'hiragana' => [
                'url' => '?hiragana=たの&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana']
                ],
            ],
            'hiragana_start' => [
                'url' => '?hiragana=た&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                ],
            ],
            'kanji' => [
                'url' => '?kanji=大人しい&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji']
                ],
            ],
            'kanji_partial' => [
                'url' => '?kanji=人&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                    self::GET_SEARCH_FIXTURES['kanji'],
                ],
            ],
            'katakana' => [
                'url' => '?katakana=ユニ&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'katakana_start' => [
                'url' => '?katakana=ニ&romaji=pagination',
                'expected' => [],
            ],

            'meaning' => [
                'url' => '?meaning[lang]=en&meaning[search]=search-me&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                    self::GET_SORT_FIXTURES['search_and_sort'][1],
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                ],
            ],
            'meaning_insensitive' => [
                'url' => '?meaning[lang]=en&meaning[search]=quiCk&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['meaning']
                ],
            ],
            'meaning_lang' => [
                'url' => '?meaning[lang]=fr&meaning[search]=loin&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji']
                ],
            ],
            'meaning_lang_unknown' => [
                'url' => '?meaning[lang]=dummy&[search]=round&romaji=paginationma',
                'expected' => self::GET_SORT_FIXTURES['search_and_sort'],
            ],
            'meaning_lang_missing' => [
                'url' => '?meaning[search]=round&romaji=paginationma',
                'expected' => self::GET_SORT_FIXTURES['search_and_sort'],
            ],
            'meaning_search_missing' => [
                'url' => '?meaning[lang]=en&romaji=paginationma',
                'expected' => self::GET_SORT_FIXTURES['search_and_sort'],
            ],
            'romaji' => [
                'url' => '?romaji=paginationTō',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji']
                ],
            ],
            'romaji_start' => [
                'url' => '?romaji=paginationTa&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                    self::GET_SEARCH_FIXTURES['hiragana'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider searchAdjectiveProvider
     *
     * @param array<string> $expected
     */
    public function testAdjectivesGetSearch(
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
        $this->assertArraySubset(
            array_slice($expected, 0, $this->getItemsPerPage()),
            $content['hydra:member']
        );
        $this->assertMatchesResourceCollectionJsonSchema(Adjective::class);
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
