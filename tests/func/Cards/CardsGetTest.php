<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\{Adjective, Noun, Verb};

/**
 * @internal
 *
 * @coversNothing
 */
class CardsGetTest extends ApiTestCase
{
    private const GET_SEARCH_FIXTURES = [
        'adjectives' => [
            'hiragana' => [
                'hiragana' => 'あかるい',
                'group' => 'i',
                'meaning' => [
                    'en' => ['light; bright; well-lit; well-lighted'],
                ],
            ],
            'katakana' => [
                'katakana' => 'アバウト',
                'group' => 'na',
                'meaning' => [
                    'en' => ['approximate (number); rough (calculation)'],
                ],
            ],
            'kanji' => [
                'kanji' => '甘い',
                'hiragana' => 'あまい',
                'group' => 'i',
                'meaning' => [
                    'en' => ['sweet'],
                ],
            ],
        ],
        'nouns' => [
            'hiragana' => [
                'hiragana' => 'あさ',
                'meaning' => [
                    'en' => ['morning'],
                ],
            ],
            'katakana' => [
                'katakana' => 'アルコール',
                'meaning' => [
                    'en' => ['alcoholic drink; alcohol'],
                ],
            ],
            'kanji' => [
                'kanji' => '飴',
                'hiragana' => 'あめ',
                'meaning' => [
                    'en' => ['(hard) candy; toffee'],
                ],
            ],
        ],
        'verbs' => [
            'hiragana' => [
                'hiragana' => 'あげる',
                'group' => 'ichidan',
                'meaning' => [
                    'en' => ['to raise'],
                ],
                'inflections' => [
                    'dictionary' => 'あげる',
                ],
            ],
            'katakana' => [
                'katakana' => 'バグる',
                'group' => 'godan',
                'meaning' => [
                    'en' => ['
                        to behave buggily (of software); 
                        to act up; 
                        to behave strangely
                    '],
                ],
                'inflections' => [
                    'dictionary' => 'バグる',
                ],
            ],
            'kanji' => [
                'kanji' => '洗う',
                'hiragana' => 'あらう',
                'group' => 'godan',
                'meaning' => [
                    'en' => ['to wash'],
                ],
                'inflections' => [
                    'dictionary' => '洗う',
                ],
            ],
        ],
    ];

    private const FIXTURES_CLASS = [
        'adjectives' => Adjective::class,
        'nouns' => Noun::class,
        'verbs' => Verb::class,
    ];

    public static function setUpBeforeClass(): void
    {
        $fixtures = self::GET_SEARCH_FIXTURES;
        foreach (self::GET_SEARCH_FIXTURES as $type => $payloads) {
            foreach ($payloads as $payload) {
                $payload['romaji'] = 'card'.
                    Noun::toRomaji($payload['hiragana'] ?? $payload['katakana'])
                ;
                static::createClient()->request(
                    'POST',
                    '/api/cards/'.$type,
                    ['json' => $payload]
                );
    
                static::assertResponseStatusCodeSame(201);
                static::assertMatchesResourceItemJsonSchema(
                    self::FIXTURES_CLASS[$type]
                );
            }
        }
    }

    private function getItemsPerPage(): int 
    {
        return (int) $_ENV["ITEMS_PER_PAGE"];
    }

    
    /**
     * @return array<array<array<string>>>
     */
    public function searchCardProvider(): array
    {
        return [
            'adjectives_hiragana' => [
                'url' => '?romaji=card&hiragana=あかるい',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['hiragana'],
                ],
                'class' => 'adjectives',
            ],
            'adjectives_katakana' => [
                'url' => '?romaji=card&katakana=アバウト',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['katakana'],
                ],
                'class' => 'adjectives',
            ],
            'adjectives_kanji' => [
                'url' => '?romaji=card&kanji=甘い',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['kanji'],
                ],
                'class' => 'adjectives',
            ],
            'adjectives_romaji' => [
                'url' => '?romaji=cardamai',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['kanji'],
                ],
                'class' => 'adjectives',
            ],
            /*
            'adjectives_meaning' => [
                'url' => 
                    '?romaji=card&meaning["lang"]=en&meaning["search"]=bright',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['hiragana'],
                ],
                'class' => 'adjectives',
            ],
            'adjectives_inflections_hiragana' => [
                'url' => 
                    '?romaji=card&hiragana=あまくない',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['kanji'],
                ],
                'class' => 'adjectives',
            ],
            'adjectives_inflections_kanji' => [
                'url' => 
                    '?romaji=card&kanji=甘くなかった',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['adjectives']['kanji'],
                ],
                'class' => 'adjectives',
            ],
            'nouns_hiragana' => [
                'url' => '?romaji=card&hiragana=あさ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['nouns']['hiragana'],
                ],
                'class' => 'nouns',
            ],
            'nouns_katakana' => [
                'url' => '?romaji=card&katakana=アルコ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['nouns']['katakana'],
                ],
                'class' => 'nouns',
            ],
            'nouns_kanji' => [
                'url' => '?romaji=card&kanji=飴',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['nouns']['kanji'],
                ],
                'class' => 'nouns',
            ],
            'nouns_romaji' => [
                'url' => '?romaji=cardaruko',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['nouns']['katakana'],
                ],
                'class' => 'nouns',
            ],
            'nouns_meaning' => [
                'url' => 
                    '?romaji=card&meaning["lang"]=en&meaning["search"]=toffee',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['nouns']['kanji'],
                ],
                'class' => 'nouns',
            ],
            /*
            'verbs_hiragana' => [
                'url' => '?romaji=card&hiragana=あげ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['hiragana'],
                ],
                'class' => 'verbs',
            ],
            'verbs_katakana' => [
                'url' => '?romaji=card&katakana=バグ',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['katakana'],
                ],
                'class' => 'verbs',
            ],
            'verbs_kanji' => [
                'url' => '?romaji=card&kanji=洗',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['kanji'],
                ],
                'class' => 'verbs',
            ],
            'verbs_romaji' => [
                'url' => '?romaji=cardbagu',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['kanji'],
                ],
                'class' => 'verbs',
            ],
            'verbs_meaning' => [
                'url' => 
                    '?romaji=card&meaning["lang"]=en&'.
                    'meaning["search"]=to act up',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['katakana'],
                ],
                'class' => 'verbs',
            ],
            'verbs_inflections' => [
                'url' => 
                    '?romaji=card&kanji=洗わせられない',
                'expected' => [
                    self::GET_SEARCH_FIXTURES['verbs']['hiragana'],
                ],
                'class' => 'verbs',
            ],
            */
        ];
    }

    /**
     * @dataProvider searchCardProvider
     *
     * @param array<string> $expected
     */
    public function testCardsGetSearch(
        string $url,
        array $expected,
        string $class,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cardsppp'.$url,
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
        $this->assertMatchesResourceCollectionJsonSchema(
            self::FIXTURES_CLASS[$class]
        );
    }

    /**
     * @return array<array<string, array<array<string, string>>|string>>
     */
    public function sortCardProvider(): array
    {
        return [
            'romaji_asc' => [
                'url' => '?romaji=card&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['adjectives']['katakana'],
                    self::GET_SORT_FIXTURES['verbs']['hiragana'],
                    self::GET_SORT_FIXTURES['adjectives']['hiragana'],
                    self::GET_SORT_FIXTURES['adjectives']['kanji'],
                    self::GET_SORT_FIXTURES['nouns']['kanji'],
                ],
            ],
            'romaji_desc' => [
                'url' => '?romaji=card&order[romaji]=desc',
                'expected' => [
                    self::GET_SORT_FIXTURES['verbs']['katakana'],
                    self::GET_SORT_FIXTURES['nouns']['katakana'],
                    self::GET_SORT_FIXTURES['verbs']['kanji'],
                    self::GET_SORT_FIXTURES['nouns']['kanji'],
                    self::GET_SORT_FIXTURES['adjectives']['kanji'],
                ],
            ],
            'search_and_order' => [
                'url' => '?hiragana=あ&order[romaji]=asc',
                'expected' => [
                    self::GET_SORT_FIXTURES['verbs']['hiragana'],
                    self::GET_SORT_FIXTURES['adjectives']['hiragana'],
                    self::GET_SORT_FIXTURES['adjectives']['kanji'],
                    self::GET_SORT_FIXTURES['nouns']['kanji'],
                    self::GET_SORT_FIXTURES['verbs']['kanji'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortCardProvider
     *
     * @param array<array<string>> $expected
     */
    public function testCardsGetSort(
        string $url,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'GET',
            '/api/cards'.$url,
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

    public function testCardsGetUnknown(): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards?katakana=ダミー',
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
    public function testCardsGetEmptyCollection(string $url): void
    {
        static::createClient()->request(
            'GET',
            '/api/cards'.$url,
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

    public function testCardsNotAllowedMethods(): void
    {
        $notAllowedMethods = ['POST', 'DELETE', 'PUT', 'PATCH'];

        foreach ($notAllowedMethods as $notAllowedMethod) {
            $payload = $notAllowedMethod !== 'DELETE' ?
                ['json' => self::GET_SEARCH_FIXTURES['adjectives']['hiragana']]
                : '';
            static::createClient()->request(
                $notAllowedMethod,
                '/api/cards',
                $payload
            );
            $this->assertResponseStatusCodeSame(405);    
        }
    }

    public function testCardsGetPagination(): void
    {
        $totalItems = count(array_merge(
                array_values(self::GET_SEARCH_FIXTURES['adjectives']),
                array_values(self::GET_SEARCH_FIXTURES['nouns']),
                array_values(self::GET_SEARCH_FIXTURES['verbs']),
            )
        );
        $itemsPerPage = $this->getItemsPerPage();
        $lastPage = ceil($totalItems / $itemsPerPage);
        
        $response = static::createClient()->request(
            'GET',
            '/api/cards?romaji=card',
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
                'hydra:first' => '/api/cards?romaji=card&page=1',
                'hydra:last' => '/api/cards?romaji=card&page='.$lastPage,
                'hydra:next' => '/api/cards?romaji=card&page=2',
            ],
        ]);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);

        $response = static::createClient()->request(
            'GET',
            '/api/cards?romaji=card&order[romaji]=desc&page=2',
        );
        $this->assertResponseStatusCodeSame(200);
        $content = json_decode($response->getContent(), true);
        $this->assertCount($itemsPerPage, $content['hydra:member']);
        $this->assertArraySubset(
            self::GET_SEARCH_FIXTURES['verbs']['hiragana'],
            $content['hydra:member'][2]
        );

        // client-side pagination options should be disabled
        $response = static::createClient()->request(
            'GET',
            '/api/cards?romaji=card&pagination=false',
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
            '/api/cards?romaji=card&itemsPerPage=1',
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
