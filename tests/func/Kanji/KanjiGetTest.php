<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Kanji;

/**
 * @internal
 *
 * @coversNothing
 */
class KanjiGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'kanji' => [
            'kanji' => '天',
            'kunyomi' => ['amatsu', 'ame', 'ama'],
            'onyomi' => ['ten'],
            'meaning' => [
                'en' => ['heavens', 'sky', 'imperial'],
            ],
        ],
        'kunyomi' => [
            'kanji' => '体',
            'kunyomi' => ['karada', 'katachi'],
            'onyomi' => ['tai', 'tei'],
            'meaning' => [
                'en' => [
                    'body', 'substance', 'object',
                    'reality', 'counter for images',
                ],
            ],
        ],
        'onyomi' => [
            'kanji' => '大',
            'kunyomi' => ['oo'],
            'onyomi' => ['dai', 'tai'],
            'meaning' => [
                'en' => ['large; big'],
            ],
        ],
        'meaning' => [
            'kanji' => '太',
            'kunyomi' => ['futo'],
            'onyomi' => ['tai', 'ta'],
            'meaning' => [
                'en' => ['plump; thick; big around'],
                'fr' => ['gras; dodu; gros'],
            ],
        ],
        'same_onyomi_1' => [
            'kanji' => '隊',
            'onyomi' => ['tai'],
            'meaning' => [
                'en' => ['party; company; squad'],
            ],
        ],
        'same_onyomi_2' => [
            'kanji' => '袋',
            'kunyomi' => ['fukuro'],
            'onyomi' => ['tai'],
            'meaning' => [
                'en' => ['sack; bag; pouch'],
                'fr' => ['sac; sacoche; pochette'],
            ],
        ],
        'same_onyomi_3' => [
            'kanji' => '帯',
            'kunyomi' => ['o', 'obi'],
            'onyomi' => ['tai'],
            'meaning' => [
                'en' => ['sash', 'belt; obi', 'zone; region'],
            ],
        ],
    ];

    private const GET_EXPECTED_KANJI = [
        'kanji' => [
            'kanji' => '天',
            'kunyomi' => ['あまつ', 'あめ', 'あま'],
            'onyomi' => ['テン'],
            'meaning' => [
                'en' => ['heavens', 'sky', 'imperial'],
            ],
        ],
        'kunyomi' => [
            'kanji' => '体',
            'kunyomi' => ['からだ', 'かたち'],
            'onyomi' => ['タイ', 'テイ'],
            'meaning' => [
                'en' => [
                    'body', 'substance', 'object',
                    'reality', 'counter for images',
                ],
            ],
        ],
        'onyomi' => [
            'kanji' => '大',
            'kunyomi' => ['おお'],
            'onyomi' => ['ダイ', 'タイ'],
            'meaning' => [
                'en' => ['large; big'],
            ],
        ],
        'meaning' => [
            'kanji' => '太',
            'kunyomi' => ['ふと'],
            'onyomi' => ['タイ', 'タ'],
            'meaning' => [
                'en' => ['plump; thick; big around'],
                'fr' => ['gras; dodu; gros'],
            ],
        ],
        'same_onyomi_1' => [
            'kanji' => '隊',
            'onyomi' => ['タイ'],
            'meaning' => [
                'en' => ['party; company; squad'],
            ],
        ],
        'same_onyomi_2' => [
            'kanji' => '袋',
            'kunyomi' => ['ふくろ'],
            'onyomi' => ['タイ'],
            'meaning' => [
                'en' => ['sack; bag; pouch'],
                'fr' => ['sac; sacoche; pochette'],
            ],
        ],
        'same_onyomi_3' => [
            'kanji' => '帯',
            'kunyomi' => ['お', 'おび'],
            'onyomi' => ['タイ'],
            'meaning' => [
                'en' => ['sash', 'belt; obi', 'zone; region'],
            ],
        ],
    ];

    private const GET_PAGINATION_FIXTURES = [
        self::GET_EXPECTED_KANJI['kunyomi'],
        self::GET_EXPECTED_KANJI['onyomi'],
        self::GET_EXPECTED_KANJI['meaning'],
        self::GET_EXPECTED_KANJI['same_onyomi_1'],
        self::GET_EXPECTED_KANJI['same_onyomi_2'],
        self::GET_EXPECTED_KANJI['same_onyomi_3'],
    ];

    public static function setUpBeforeClass(): void
    {
        foreach (self::GET_SEARCH_FIXTURES as $payload) {
            static::createClient()->request(
                'POST',
                '/api/cards/kanji',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Kanji::class);
        }
    }

    /**
     * @return array<array<array<string>>>
     */
    public function searchKanjiProvider(): array
    {
        return [
            'kanji' => [
                'url' => '?kanji=天',
                'expected' => [
                    self::GET_EXPECTED_KANJI['kanji'],
                ],
            ],
            'kunyomi_hiragana' => [
                'url' => '?kunyomi=からだ',
                'expected' => [
                    self::GET_EXPECTED_KANJI['kunyomi'],
                ],
            ],
            'kunyomi_romaji' => [
                'url' => '?kunyomi=karada',
                'expected' => [
                    self::GET_EXPECTED_KANJI['kunyomi'],
                ],
            ],
            'kunyomi_katakana' => [
                'url' => '?kunyomi=カラダ',
                'expected' => [],
            ],
            'kunyomi_kanji' => [
                'url' => '?kunyomi=体',
                'expected' => [],
            ],
            'onyomi_katakana' => [
                'url' => '?onyomi=ダイ',
                'expected' => [
                    self::GET_EXPECTED_KANJI['onyomi'],
                ],
            ],
            'onyomi_romaji' => [
                'url' => '?onyomi=ta',
                'expected' => [
                    self::GET_EXPECTED_KANJI['meaning'],
                ],
            ],
            'onyomi_hiragana' => [
                'url' => '?onyomi=たい',
                'expected' => [],
            ],
            'onyomi_kanji' => [
                'url' => '?onyomi=大',
                'expected' => [],
            ],
            'meaning' => [
                'url' => '?meaning[lang]=en&meaning[search]=big around',
                'expected' => [
                    self::GET_EXPECTED_KANJI['meaning'],
                ],
            ],
            'meaning_insensitive' => [
                'url' => '?meaning[lang]=en&meaning[search]=realItY',
                'expected' => [
                    self::GET_EXPECTED_KANJI['kunyomi'],
                ],
            ],
            'meaning_lang' => [
                'url' => '?meaning[lang]=fr&meaning[search]=sacoche',
                'expected' => [
                    self::GET_EXPECTED_KANJI['same_onyomi_2'],
                ],
            ],
            'meaning_lang_unknown' => [
                'url' => '?meaning[lang]=dummy&meaning[search]=substance&onyomi=タイ',
                'expected' => [],
            ],
            'meaning_lang_missing' => [
                'url' => '?meaning[search]=large&onyomi=タイ',
                'expected' => self::GET_PAGINATION_FIXTURES,
            ],
            'meaning_search_missing' => [
                'url' => '?meaning[lang]=en&onyomi=タイ',
                'expected' => self::GET_PAGINATION_FIXTURES,
            ],
        ];
    }

    /**
     * @dataProvider searchKanjiProvider
     *
     * @param array<string> $expected
     */
    public function testKanjiGetSearch(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/kanji'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Kanji::class);
    }

    public function testKanjiGetOneKanji(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/cards/kanji?kunyomi=ふと'
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_EXPECTED_KANJI['meaning'],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Kanji::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testKanjiGetOneKanji
     */
    public function testKanjiGetOneKanjiByCode(string $code): void
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
            self::GET_EXPECTED_KANJI['meaning']
        );

        $this->assertMatchesResourceItemJsonSchema(Kanji::class);
    }

    public function testKanjiGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/kanji/dummy',
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<array<string>>
     */
    public function emptyCollectionProvider(): array
    {
        return [
            'field' => ['?kunyomi=っ'],
        ];
    }

    /**
     * @dataProvider emptyCollectionProvider
     */
    public function testKanjiGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/kanji'.$url,
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

    public function testKanjiGetPagination(): void
    {
        $totalItems = count(self::GET_PAGINATION_FIXTURES);
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);

        $itemsOnSecondPage = $totalItems - $itemsPerPage;
        if ($itemsOnSecondPage > $itemsPerPage) {
            $itemsOnSecondPage = $itemsPerPage;
        }

        $response = static::createClient()->request(
            'GET',
            '/api/cards/kanji?onyomi=タイ',
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
                'hydra:first' => '/api/cards/kanji?onyomi='.urlencode('タイ').'&page=1',
                'hydra:last' => '/api/cards/kanji?onyomi='.
                    urlencode('タイ').'&page='.$lastPage,
                'hydra:next' => '/api/cards/kanji?onyomi='.urlencode('タイ').'&page=2',
            ],
        ]);
        $firstPageContent = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $firstPageContent['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/kanji?onyomi=タイ&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $secondPageContent = json_decode($response->getContent(), true);
        $this->assertCount(
            $itemsOnSecondPage,
            $secondPageContent['hydra:member']
        );

        // first page results shouldn't be on second page
        $firstPageIds = $this->getIds($firstPageContent['hydra:member']);
        $secondPageIds = $this->getIds($secondPageContent['hydra:member']);
        $this->assertSame(
            $firstPageIds,
            array_diff($firstPageIds, $secondPageIds)
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards/kanji?onyomi=タイ&pagination=false',
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
            '/api/cards/kanji?onyomi=タイ&itemsPerPage=1',
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

    /**
     * @param array<mixed> $results
     *
     * @return array<string>
     */
    private function getIds(array $results): array
    {
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['@id'];
        }

        return $ids;
    }
}
