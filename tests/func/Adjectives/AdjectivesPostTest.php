<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Adjective;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class AdjectivesPostTest extends ApiTestCase
{    
    private const POST_COMPLETE_VALID_ADJECTIVES = [
        'i_adjective' => [
            'romaji' => 'kawaii',
            'hiragana' => 'かわいい',
            'kanji' => '可愛い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => 'cute, adorable, charming, lovely, pretty',
            ],
        ],
        'na_adjective' => [
            'romaji' => 'kirei',
            'hiragana' => 'きれい',
            'kanji' => '綺麗',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'pretty, lovely, beautiful, fair',
                    'clean, clear, pure, tidy, neat',
                ],
            ],
        ],
    ];

    private const POST_COMPLETE_EXPECTED_ADJECTIVES = [
        'i_adjective' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'inflections' => [
                'non-past' => [
                    'affirmative' => '可愛い',
                    'negative' => '可愛くない',
                ],
                'past' => [
                    'affirmative' => '可愛かった',
                    'negative' => '可愛くなかった',
                ],
            ],
        ],
        'na_adjective' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['na_adjective'],
            'inflections' => [
                'non-past' => [
                    'affirmative' => '綺麗',
                    'negative' => '綺麗じゃない',
                ],
                'past' => [
                    'affirmative' => '綺麗だった',
                    'negative' => '綺麗じゃなかった',
                ],
            ],
        ],
    ];
    private const POST_MINIMAL_VALID_ADJECTIVE = [
        'romaji' => 'oishii',
        'hiragana' => 'おいしい',
        'group' => 'i',
        'meaning' => [
            'en' => 'delicious',
        ]
    ];

    private const POST_INVALID_ADJECTIVES = [
        'romaji_empty' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'romaji' => '',
        ],
        'romaji_maxlength' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'romaji' => '*',
        ],
        'romaji_written_in_kana' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'romaji' => 'ローマジ',
        ],
        'no_hiragana_nor_katakana' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'hiragana' => '',
            'katakana' => '',
        ],
        'hiragana_written_in_katakana' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'hiragana' => 'カタカナ',
        ],
        'hiragana_maxlength' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'hiragana' => '*',
        ],
        'katakana_written_in_hiragana' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'katakana' => 'ひらがな',
        ],
        'katakana_maxlength' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'katakana' => '*',
        ],
        'kanji_maxlength' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'kanji' => '*',
        ],
        'kanji_written_in_romaji' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'kanji' => 'kanji',
        ],
        'meaning_empty' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'meaning' => '',
        ],
        'meaning_not_an_array' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'meaning' => 'to eat',
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'meaning' => [
                'en' => 'to eat',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'group_adjective' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'group' => 'godan',
        ],
        'jlpt_not_an_integer' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'jlpt' => 1.1,
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'jlpt' => 6,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validAdjectivesProvider(): array
    {
        $provider = [];

        foreach(self::POST_COMPLETE_VALID_ADJECTIVES as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_ADJECTIVES[$key] ?? $value;
            $provider[] = [$value, $expected];
        }
        return $provider;
    }

    /**
     * @dataProvider validAdjectivesProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testAdjectivesPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Adjective::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('createdAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['createdAt']);
        $this->assertMatchesRegularExpression(
            '/\d+-'.$expected['romaji'].'/',
            $content['code']
        );
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidAdjectivesProvider(): array
    {
        return [
            [
                self::POST_INVALID_ADJECTIVES['romaji_empty'],
                'romaji: '.Adjective::VALIDATION_ERR_EMPTY,
            ],
            [
                [
                    ...self::POST_INVALID_ADJECTIVES['romaji_maxlength'],
                    'romaji' => str_repeat(
                        'a', Adjective::ROMAJI_MAXLENGTH + 1
                    ),
                ],
                'romaji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_ADJECTIVES['romaji_written_in_kana'],
                'romaji: '.Adjective::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_ADJECTIVES['no_hiragana_nor_katakana'],
                'hiragana: '.Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_ADJECTIVES['no_hiragana_nor_katakana'],
                'katakana: '.Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_ADJECTIVES['hiragana_written_in_katakana'],
                'hiragana: '.Adjective::VALIDATION_ERR_HIRAGANA,
            ],
            [
                [
                    ...self::POST_INVALID_ADJECTIVES['hiragana_maxlength'],
                    'hiragana' => 
                        str_repeat('あ', Adjective::HIRAGANA_MAXLENGTH + 1),
                ],
                'hiragana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_ADJECTIVES['katakana_written_in_hiragana'],
                'katakana: '.Adjective::VALIDATION_ERR_KATAKANA,
            ],
            [
                [
                    ...self::POST_INVALID_ADJECTIVES['katakana_maxlength'],
                    'katakana' => 
                        str_repeat('ア', Adjective::KATAKANA_MAXLENGTH + 1),
                ],
                'katakana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                [
                    ...self::POST_INVALID_ADJECTIVES['kanji_maxlength'],
                    'kanji' => str_repeat('字', Adjective::KANJI_MAXLENGTH + 1),
                ],
                'kanji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_ADJECTIVES['kanji_written_in_romaji'],
                'kanji: '.Adjective::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_empty'],
                'meaning: '.Adjective::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_not_an_array'],
                'meaning: '.Adjective::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_lang_unknown'],
                'meaning: '.Adjective::VALIDATION_ERR_MEANING,
            ],
            [
                self::POST_INVALID_ADJECTIVES['group_adjective'],
                'group: '.Adjective::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_ADJECTIVES['jlpt_not_an_integer'],
                'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_ADJECTIVES['jlpt_min'],
                'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_ADJECTIVES['jlpt_max'],
                'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
            ],
        ];
    }

    /**
     * @dataProvider invalidAdjectivesProvider
     *
     * @param array<string> $payload
     */
    public function testAdjectivesPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }
}
