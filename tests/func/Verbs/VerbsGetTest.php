<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Verb;

/**
 * @internal
 *
 * @coversNothing
 */
class VerbsGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'hiragana' => [
            'hiragana' => 'あける',
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['to open'],
            ],
            'inflections' => [
                'dictionary' => 'あける',
            ],
        ],
        'katakana' => [
            'katakana' => 'モテる',
            'romaji' => 'paginationmoteru',
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['to be popular'],
            ],
            'inflections' => [
                'dictionary' => 'モテる',
            ],
        ],
        'katakana_2' => [
            'katakana' => 'モフる',
            'romaji' => 'paginationmofuru',
            'group' => 'godan',
            'meaning' => [
                'en' => ['to stroke (something fluffy); to rub; to pat'],
            ],
            'inflections' => [
                'dictionary' => 'モフる',
            ],
        ],
        'kanji' => [
            'hiragana' => 'しまる',
            'kanji' => '閉まる',
            'group' => 'godan',
            'meaning' => [
                'en' => ['to be shut', 'to be closed'],
                'fr' => ['être fermé'],
            ],
            'inflections' => [
                'dictionary' => '閉まる',
            ],
        ],
        'meaning' => [
            'hiragana' => 'ふさぐ',
            'kanji' => '塞ぐ',
            'group' => 'godan',
            'meaning' => [
                'en' => ['to stop up; to close up'],
            ],
            'inflections' => [
                'dictionary' => 'ふさぐ',
            ],
        ],
        'romaji' => [
            'hiragana' => 'あそぶ',
            'romaji' => 'paginationasobu',
            'group' => 'godan',
            'meaning' => [
                'en' => ['to play; to enjoy'],
            ],
            'inflections' => [
                'dictionary' => 'あそぶ',
            ],
        ],
    ];

    private const GET_SORT_FIXTURES = [
        self::GET_SEARCH_FIXTURES['hiragana'],
        self::GET_SEARCH_FIXTURES['romaji'],
        self::GET_SEARCH_FIXTURES['meaning'],
        self::GET_SEARCH_FIXTURES['katakana_2'],
        self::GET_SEARCH_FIXTURES['katakana'],
        self::GET_SEARCH_FIXTURES['kanji'],
    ];

    public static function setUpBeforeClass(): void
    {
        $fixtures = self::GET_SEARCH_FIXTURES;
        array_walk(
            $fixtures,
            fn (&$fixture) => $fixture['romaji'] ??=
                'pagination'.
                Verb::toRomaji($fixture['hiragana'] ?? $fixture['katakana'])
        );
        foreach ($fixtures as $payload) {
            static::createClient()->request(
                'POST',
                '/api/cards/verbs',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Verb::class);
        }
    }

    /**
     * @return array<array<array<string>>>
     */
    public function searchVerbsProvider(): array
    {
        return [
            'hiragana' => [
                'url' => '?hiragana=あけ&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                ],
            ],
            'hiragana_start' => [
                'url' => '?hiragana=あ&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
            'kanji' => [
                'url' => '?kanji=閉まる&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji'],
                ],
            ],
            'kanji_partial' => [
                'url' => '?kanji=塞&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['meaning'],
                ],
            ],
            'katakana' => [
                'url' => '?katakana=モテ&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'katakana_start' => [
                'url' => '?katakana=モ&romaji=pagination&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'meaning' => [
                'url' => '?meaning[lang]=en'.
                    '&meaning[search]=to close up&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['meaning'],
                ],
            ],
            'meaning_insensitive' => [
                'url' => '?meaning[lang]=en'.
                    '&meaning[search]=TO Be sHuT&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji'],
                ],
            ],
            'meaning_lang' => [
                'url' => '?meaning[lang]=fr'.
                    '&meaning[search]=être fermé&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji'],
                ],
            ],
            'meaning_lang_unknown' => [
                'url' => '?meaning[lang]=dummy'.
                    '&meaning[search]=to open&romaji=pagination',
                'expected' => [],
            ],
            'meaning_lang_missing' => [
                'url' => '?romaji=pagination&meaning[search]=to open&order[romaji]=asc',
                'expected' => self::GET_SORT_FIXTURES,
            ],
            'meaning_search_missing' => [
                'url' => '?romaji=pagination&meaning[lang]=en&order[romaji]=asc',
                'expected' => self::GET_SORT_FIXTURES,
            ],
            'romaji' => [
                'url' => '?romaji=paginationAso',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
            'romaji_start' => [
                'url' => '?romaji=paginationMO&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'inflections_hiragana' => [
                'url' => '?romaji=pagination&hiragana=あそんだ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
            'inflections_katakana' => [
                'url' => '?romaji=pagination&katakana=モテろ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'inflections_kanji' => [
                'url' => '?romaji=pagination&kanji=閉まらせられない',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['kanji'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider searchVerbsProvider
     *
     * @param array<string> $expected
     */
    public function testVerbsGetSearch(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/verbs'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Verb::class);
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortVerbsProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=paginationm&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_2'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=paginationa&order[romaji]=desc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji'],
                    self::GET_SEARCH_FIXTURES['hiragana'],
                ],
            ],
            'search_and_order' => [
                'url' => '?romaji=paginationa&hiragana=あ&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortVerbsProvider
     *
     * @param array<array<string>> $expected
     */
    public function testVerbsGetSort(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/verbs'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Verb::class);
    }

    public function testVerbsGetOneVerb(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/cards/verbs?katakana='.
            self::GET_SEARCH_FIXTURES['katakana_2']['katakana']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['katakana_2'],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Verb::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testVerbsGetOneVerb
     */
    public function testVerbsGetOneVerbByCode(string $code): void
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
            self::GET_SEARCH_FIXTURES['katakana_2']
        );

        $this->assertMatchesResourceItemJsonSchema(Verb::class);
    }

    public function testVerbsGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/verbs/dummy',
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
    public function testVerbsGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/verbs'.$url,
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

    public function testVerbsGetPagination(): void
    {
        $totalItems = count(self::GET_SEARCH_FIXTURES);
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/verbs?romaji=pagination',
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
                'hydra:first' => '/api/cards/verbs?romaji=pagination&page=1',
                'hydra:last' => '/api/cards/verbs?romaji=pagination&page='.$lastPage,
                'hydra:next' => '/api/cards/verbs?romaji=pagination&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/verbs?romaji=pagination&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $itemsOnPage2 = $totalItems - $itemsPerPage;
        if ($itemsOnPage2 > $itemsPerPage) {
            $itemsOnPage2 = $itemsPerPage;
        }
        $this->assertCount($itemsOnPage2, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['hiragana'],
            $content['hydra:member'][0]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards/verbs?romaji=pagination&pagination=false',
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
            '/api/cards/verbs?romaji=pagination&itemsPerPage=1',
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
