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
            'hiragana' => 'ねむい',
            'kanji' => '眠い',
            'group' => 'i',
            'meaning' => [
                'en' => ['sleepy'],
            ],
        ],
        'kanji_2' => [
            'hiragana' => 'ねむそう',
            'kanji' => '眠そう',
            'group' => 'na',
            'meaning' => [
                'en' => ['sleepy-looking; sleepy-sounding'],
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
                'en' => ['universal'],
            ],
        ],
        'meaning' => [
            'hiragana' => 'かんたん',
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'simple; easy; uncomplicated',
                    'brief; quick; light',
                ],
            ],
        ],
        'romaji' => [
            'hiragana' => 'とおい',
            'romaji' => 'tooi',
            'group' => 'i',
            'meaning' => [
                'en' => ['far'],
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
        'search and sort' => [
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
                        'poor; unskillful'
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

    /**
     * @return array<array<array<string>>>
     */
    public function searchAdjectiveProvider(): array
    {
        return [
            'hiragana' => [
                'url' => '?hiragana=たの',
                'expected' => self::GET_SEARCH_FIXTURES['hiragana'],
            ],
            'hiragana_partial' => [
                'url' => '?hiragana=た',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                ],
            ],
            'kanji' => [
                'url' => '?kanji=眠い',
                'expected' => self::GET_SEARCH_FIXTURES['kanji'],
            ],
            'kanji_partial' => [
                'url' => '?kanji=眠',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji'],
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                ],
            ],
            'katakana' => [
                'url' => '?katakana=ユニー',
                'expected' => self::GET_SEARCH_FIXTURES['katakana'],
            ],
            'katakana_partial' => [
                'url' => '?katakana=ユニ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana'],
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                ],
            ],
            'meaning' => [
                'url' => '?meaning=qui',
                'expected' => self::GET_SEARCH_FIXTURES['meaning'],
            ],
            'meaning_partial' => [
                'url' => '?meaning=un',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['meaning'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                ],
            ],
            'romaji' => [
                'url' => '?romaji=To',
                'expected' => self::GET_SEARCH_FIXTURES['romaji'],
            ],
            'romaji_partial' => [
                'url' => '?romaji=TaNo',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                ],
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
            '/api/cards/adjectives'.$url,
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $content = json_decode($response->getContent(), true);

        $this->assertSame($content['hydra:totalItems'], count($expected));
        $this->assertArraySubset($expected, $content['hydra:member'][0]);
        $this->assertMatchesResourceCollectionJsonSchema(Adjective::class);
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortAdjectiveProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=ra&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_asc'][1],
                    self::GET_SORT_FIXTURES['romaji_asc'][2],
                    self::GET_SORT_FIXTURES['romaji_asc'][0],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=shi&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_desc'][2],
                    self::GET_SORT_FIXTURES['romaji_desc'][0],
                    self::GET_SORT_FIXTURES['romaji_desc'][1],
                ],
            ],
            'search_and_order' => [
                'url' => '?hiragana=ま&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['search and sort'][1],
                    self::GET_SORT_FIXTURES['search and sort'][0],
                    self::GET_SORT_FIXTURES['search and sort'][2],
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
            self::GET_SORT_FIXTURES['search and sort'][0]['kanji']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['search and sort'][0],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Adjective::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testAdjectivesGetOneAdjective
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
            self::GET_SORT_FIXTURES['search and sort'][0]
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
            'hydra:totalItems' => 15,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/cards/adjectives?romaji=pagination&page=1',
                'hydra:last' => '/api/cards/adjectives?romaji=pagination&page=3',
                'hydra:next' => '/api/cards/adjectives?romaji=pagination&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives?romaji=pagination&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['description_desc'][2],
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
            'hydra:totalItems' => 15,
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/adjectives?romaji=pagination&itemsPerPage=1',
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 15,
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content['hydra:member']);
    }
}
