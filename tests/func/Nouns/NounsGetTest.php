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
            'hiragana' => 'かめ',
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
        'bikago_o' => [
            'hiragana' => 'かし',
            'kanji' => '菓子',
            'bikago' => 'お',
            'meaning' => [
                'en' => ['sweets; candy'],
            ],
        ],
        'bikago_go' => [
            'hiragana' => 'けっこん',
            'kanji' => '結婚',
            'bikago' => 'ご',
            'meaning' => [
                'en' => ['marriage'],
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
        'romaji_2' => [
            'katakana' => 'タコ',
            'meaning' => [
                'en' => ['octopus'],
            ],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        array_walk(self::GET_SEARCH_FIXTURES, fn(&$fixture) => 
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
            'bikago_o_hiragana' => [
                'url' => '?hiragana=おかし&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['bikago_o'],
                ],
            ],
            'bikago_o_kanji' => [
                'url' => '?kanji=お菓子&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['bikago_o'],
                ],
            ],
            'bikago_go_hiragana' => [
                'url' => '?hiragana=ごけっこん&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['bikago_go'],
                ],
            ],
            'bikago_go_kanji' => [
                'url' => '?kanji=ご結婚&romaji=pagination',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['bikago_go'],
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
    public function sortNounsProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=paginationt&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji_2'][1],
                    self::GET_SEARCH_FIXTURES['hiragana'][2],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=paginationk&order[romaji]=desc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['meaning'],
                    self::GET_SEARCH_FIXTURES['kanji_2'],
                ],
            ],
            'search_and_order' => [
                'url' => '?hiragana=う&order[romaji]=desc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana_3'],
                    self::GET_SEARCH_FIXTURES['kanji_3'],
                    self::GET_SEARCH_FIXTURES['hiragana_2'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortNounsProvider
     *
     * @param array<array<string>> $expected
     */
    public function testNounsGetSort(
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
        for ($i = 0; $i < count($expected); ++$i) {
            $this->assertArraySubset(
                $expected[$i],
                $content['hydra:member'][$i]
            );
        }
        $this->assertMatchesResourceCollectionJsonSchema(Noun::class);
    }

    public function testNounsGetOneNoun(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/cards/nouns?kanji='.
            self::GET_SEARCH_FIXTURES['kanji_2']['kanji']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['kanji_2'],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Noun::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testNounsGetOneNoun
     */
    public function testNounsGetOneNounByCode(string $code): void
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
            self::GET_SEARCH_FIXTURES['kanji_2']
        );

        $this->assertMatchesResourceItemJsonSchema(Noun::class);
    }

    public function testNounsGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/nouns/dummy',
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
    public function testNounsGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/nouns'.$url,
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

    public function testNounsGetPagination(): void
    {
        $totalItems = count(self::GET_SEARCH_FIXTURES);
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);
        
        $response = static::createClient()->request(
            'GET',
            '/api/cards/nouns?romaji=pagination',
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
            '/api/cards/nouns?romaji=pagination&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['romaji_2'],
            $content['hydra:member'][1]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards/nouns?romaji=pagination&pagination=false',
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
            '/api/cards/nouns?romaji=pagination&itemsPerPage=1',
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
