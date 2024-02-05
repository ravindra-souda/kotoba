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
            'hiragana' => 'かわいい',
            'kanji' => '可愛い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => 'cute, adorable, charming, lovely, pretty',
            ],
        ],
        'na_adjective' => [
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
        'na_adjective_katakana' => [
            'katakana' => 'オリジナル',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'original',
                    'unique, exclusive',
                ],
            ],
        ],
        'romaji_filled' => [
            'romaji' => 'original',
            'katakana' => 'オリジナル',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'original',
                    'unique, exclusive',
                ],
            ],
        ],
    ];

    private const POST_COMPLETE_EXPECTED_ADJECTIVES = [
        'i_adjective' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'romaji' => 'kawaii',
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
            'romaji' => 'kirei',
            'inflections' => [
                'non-past' => [
                    'affirmative' => '綺麗',
                    'negative' => '綺麗じゃない',
                ],
                'past' => [
                    'affirmative' => '綺麗でした',
                    'negative' => '綺麗じゃなかった',
                ],
            ],
        ],
        'na_adjective_katakana' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['na_adjective_katakana'],
            'romaji' => 'orijinaru',
            'inflections' => [
                'non-past' => [
                    'affirmative' => 'オリジナル',
                    'negative' => 'オリジナルじゃない',
                ],
                'past' => [
                    'affirmative' => 'オリジナルでした',
                    'negative' => 'オリジナルじゃなかった',
                ],
            ],
        ],
        'romaji_filled' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['romaji_filled'],
            'inflections' => [
                'non-past' => [
                    'affirmative' => 'オリジナル',
                    'negative' => 'オリジナルじゃない',
                ],
                'past' => [
                    'affirmative' => 'オリジナルでした',
                    'negative' => 'オリジナルじゃなかった',
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
            'meaning' => [],
        ],
        'meaning_mandatory_lang_missing' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'meaning' => [
                'fr' => 'delicieux',
            ],
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_ADJECTIVE,
            'meaning' => [
                'en' => 'delicious',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'group_adjective' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'group' => 'dummy',
        ],
        'group_invalid_hiragana' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'hiragana' => 'すてき',
        ],
        'group_invalid_kanji' => [
            ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
            'kanji' => '素敵',
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
                [
                    ...self::POST_INVALID_ADJECTIVES['romaji_maxlength'],
                    'romaji' => str_repeat(
                        'x', Adjective::ROMAJI_MAXLENGTH + 1
                    ),
                ],
                'romaji: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MAXLENGTH, 
                    Adjective::ROMAJI_MAXLENGTH
                )
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
                'hiragana: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MAXLENGTH, 
                    Adjective::HIRAGANA_MAXLENGTH
                )
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
                'katakana: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MAXLENGTH, 
                    Adjective::KATAKANA_MAXLENGTH
                ),
            ],
            [
                [
                    ...self::POST_INVALID_ADJECTIVES['kanji_maxlength'],
                    'kanji' => str_repeat('字', Adjective::KANJI_MAXLENGTH + 1),
                ],
                'kanji: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MAXLENGTH, 
                    Adjective::KANJI_MAXLENGTH
                ),
            ],
            [
                self::POST_INVALID_ADJECTIVES['kanji_written_in_romaji'],
                'kanji: '.Adjective::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_empty'],
                'meaning: '.Adjective::VALIDATION_ERR_EMPTY,
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_mandatory_lang_missing'],
                'meaning: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MEANING[1], 
                    Adjective::getMandatoryLang(),
                )
            ],
            [
                self::POST_INVALID_ADJECTIVES['meaning_lang_unknown'],
                'meaning: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_MEANING[2], 
                    Adjective::getAllowedLangs()
                )
            ],
            [
                self::POST_INVALID_ADJECTIVES['group_adjective'],
                'group: '.Adjective::formatMsg(
                    Adjective::VALIDATION_ERR_ENUM, 
                    Adjective::ALLOWED_GROUPS,
                )
            ],
            [
                self::POST_INVALID_ADJECTIVES['group_invalid_hiragana'],
                'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[1]
            ],
            [
                self::POST_INVALID_ADJECTIVES['group_invalid_kanji'],
                'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[2]
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
