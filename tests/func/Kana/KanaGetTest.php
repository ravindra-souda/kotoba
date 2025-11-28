<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Kana;

/**
 * @internal
 *
 * @coversNothing
 */
class KanaGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'hiragana' => [
            'hiragana' => 'き',
        ],
        'hiragana_glide' => [
            'hiragana' => 'きゃ',
        ],
        'katakana' => [
            'katakana' => 'キ',
        ],
        'katakana_glide' => [
            'katakana' => 'キャ',
        ],
        'romaji' => [
            'hiragana' => 'く',
        ],
    ];
    private const GET_SORT_FIXTURES = [
        'romaji_asc' => [
            [
                'hiragana' => 'り',
            ],
            [
                'katakana' => 'ル',
            ],
            [
                'hiragana' => 'ら',
            ],
        ],
        'romaji_desc' => [
            [
                'katakana' => 'シ',
            ],
            [
                'katakana' => 'ス',
            ],
            [
                'hiragana' => 'そ',
            ],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        $fixtures = array_merge_recursive(
            array_values(self::GET_SEARCH_FIXTURES),
            ...array_values(self::GET_SORT_FIXTURES),
        );
        array_walk(
            $fixtures,
            fn (&$fixture) => $fixture['romaji'] = substr(
                'x'.
                    Kana::toRomaji($fixture['hiragana'] ?? $fixture['katakana']),
                0,
                4
            )
        );
        foreach ($fixtures as $payload) {
            static::createClient()->request(
                'POST',
                '/api/cards/kana',
                ['json' => $payload]
            );

            static::assertResponseStatusCodeSame(201);
            static::assertMatchesResourceItemJsonSchema(Kana::class);
        }
    }

    /**
     * @return array<array<array<array<string>>|string>>
     */
    public function searchKanaProvider(): array
    {
        return [
            'hiragana' => [
                'url' => '?hiragana=き&romaji=x',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                ],
            ],
            'hiragana_glide' => [
                'url' => '?hiragana=きゃ&romaji=x',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana_glide'],
                ],
            ],
            'katakana' => [
                'url' => '?katakana=キ&romaji=x',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'katakana_glide' => [
                'url' => '?katakana=キャ&romaji=x',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['katakana_glide'],
                ],
            ],
            'romaji' => [
                'url' => '?romaji=xku',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['romaji'],
                ],
            ],
            'romaji_hiragana_and_katakana' => [
                'url' => '?romaji=xki',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                ],
            ],
            'romaji_start' => [
                'url' => '?romaji=xK&order[romaji]=asc',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['hiragana'],
                    self::GET_SEARCH_FIXTURES['katakana'],
                    self::GET_SEARCH_FIXTURES['romaji'],
                    self::GET_SEARCH_FIXTURES['hiragana_glide'],
                    self::GET_SEARCH_FIXTURES['katakana_glide'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider searchKanaProvider
     *
     * @param array<string> $expected
     */
    public function testKanaGetSearch(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/kana'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Kana::class);
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortKanaProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=xr&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_asc'][2],
                    self::GET_SORT_FIXTURES['romaji_asc'][0],
                    self::GET_SORT_FIXTURES['romaji_asc'][1],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=xs&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['romaji_desc'][1],
                    self::GET_SORT_FIXTURES['romaji_desc'][2],
                    self::GET_SORT_FIXTURES['romaji_desc'][0],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortKanaProvider
     *
     * @param array<array<string>> $expected
     */
    public function testKanaGetSort(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards/kana'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(Kana::class);
    }

    public function testKanaGetOneKana(): string
    {
        $response = static::createClient()->request(
            'GET',
            'api/cards/kana?romaji=x&katakana='.
            self::GET_SEARCH_FIXTURES['katakana_glide']['katakana']
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $content = json_decode($response->getContent(), true);
        $this->assertSame($content['hydra:totalItems'], 1);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['katakana_glide'],
            $content['hydra:member'][0]
        );
        $this->assertMatchesResourceCollectionJsonSchema(Kana::class);

        return $content['hydra:member'][0]['@id'];
    }

    /**
     * @depends testKanaGetOneKana
     */
    public function testKanaGetOneKanaByCode(string $code): void
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
            self::GET_SEARCH_FIXTURES['katakana_glide']
        );

        $this->assertMatchesResourceItemJsonSchema(Kana::class);
    }

    public function testKanaGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/kana/dummy',
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
    public function testKanaGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards/kana'.$url,
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

    public function testKanaGetPagination(): void
    {
        $totalItems = count(
            array_merge_recursive(
                array_values(self::GET_SEARCH_FIXTURES),
                ...array_values(self::GET_SORT_FIXTURES),
            )
        );
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/kana?romaji=x',
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
                'hydra:first' => '/api/cards/kana?romaji=x&page=1',
                'hydra:last' => '/api/cards/kana?romaji=x&page='.$lastPage,
                'hydra:next' => '/api/cards/kana?romaji=x&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards/kana?romaji=x&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SORT_FIXTURES['romaji_asc'][2],
            $content['hydra:member'][0]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards/kana?romaji=x&pagination=false',
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
            '/api/cards/kana?romaji=x&itemsPerPage=1',
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
