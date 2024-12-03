<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Adjective;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class AdjectivesPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_FIXTURE_ADJECTIVES = [
        'i_adjective' => [
            'hiragana' => 'おもしろい',
            'kanji' => '面白い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => ['interesting'],
            ],
        ],
        'na_adjective' => [
            'hiragana' => 'ゆうめい',
            'kanji' => '有名',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => ['famous, well-known'],
            ],
        ],
        'na_adjective_katakana' => [
            'katakana' => 'クール',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => ['cool'],
            ],
        ],
        'romaji_filled' => [
            'romaji' => 'cool',
            'katakana' => 'クール',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => ['cool'],
            ],
        ],
    ];

    private const PUT_VALID_ADJECTIVES = [
        'hiragana' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'hiragana' => '   おかしい  ',
        ],
        'katakana' => [
            ...self::PUT_FIXTURE_ADJECTIVES['na_adjective_katakana'],
            'katakana' => '   オリジナル   ',
        ],
        'romaji' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'romaji' => '  okashii  ',
        ],
        'kanji' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'hiragana' => 'おかしい',
            'kanji' => '  可笑しい ',
        ],
        'jlpt' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'jlpt' => 4,
        ],
        'group' => [
            ...self::PUT_FIXTURE_ADJECTIVES['na_adjective'],
            'hiragana' => '  なだかい   ',
            'kanji' => ' 名高い    ',
            'group' => 'i',
        ],
        'meaning' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'meaning' => [
                'en' => [
                    'interesting, fascinating, intriguing, enthralling',
                    'amusing, funny, comical',
                ],
            ],
        ],
        'meaning_new_lang' => [
            ...self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            'meaning' => [
                'en' => [
                    'interesting',
                    'amusing',
                ],
                'fr' => [
                    'intéressant',
                    'amusant',
                ],
            ],
        ],
    ];

    private const PUT_INVALID_ADJECTIVES = [
        'romaji_maxlength' => [
            'fixture' => 'i_adjective',
            'maxlength' => [
                'romaji' => 'r',
            ],
            'message' => [
                'text' => 'romaji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::ROMAJI_MAXLENGTH,
            ],
        ],
        'romaji_written_in_kana' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'romaji' => 'ろうまじ',
            ],
            'message' => 'romaji: '.Adjective::VALIDATION_ERR_ROMAJI,
        ],
        'no_hiragana_nor_katakana' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'hiragana' => ' ',
                'katakana' => '    ',
            ],
            'message' => 'hiragana: '.
                Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_written_in_katakana' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'hiragana' => 'オモシロイ',
            ],
            'message' => 'hiragana: '.Adjective::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'fixture' => 'i_adjective',
            'maxlength' => [
                'hiragana' => 'い',
            ],
            'message' => [
                'text' => 'hiragana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'fixture' => 'na_adjective_katakana',
            'payload' => [
                'katakana' => 'くうる',
            ],
            'message' => 'katakana: '.Adjective::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'fixture' => 'na_adjective_katakana',
            'maxlength' => [
                'katakana' => 'イ',
            ],
            'message' => [
                'text' => 'katakana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::KATAKANA_MAXLENGTH,
            ],
        ],
        'katakana_halfwidth' => [
            'fixture' => 'na_adjective_katakana',
            'payload' => [
                'katakana' => 'ｸｰﾙ',
            ],
            'message' => 'katakana: '.Adjective::VALIDATION_ERR_KATAKANA,
        ],
        'kanji_maxlength' => [
            'fixture' => 'i_adjective',
            'maxlength' => [
                'kanji' => '物',
            ],
            'message' => [
                'text' => 'kanji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::KANJI_MAXLENGTH,
            ],
        ],
        'kanji_written_in_full_hiragana' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'kanji' => 'おもしろい',
            ],
            'message' => 'kanji: '.Adjective::VALIDATION_ERR_KANJI,
        ],
        'meaning_mandatory_lang_missing' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'meaning' => [
                    'fr' => ['amusant'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[1],
                'values' => Adjective::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_lang_unknown' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'meaning' => [
                    'en' => ['funny'],
                    'dummy' => ['🂡🂱🃁🃑'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[2],
                'values' => Adjective::ALLOWED_LANGS,
            ],
        ],
        'meaning_empty' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'meaning' => [
                    'en' => [' '],
                ],
            ],
            'message' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[3],
        ],
        'meaning_invalid' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'meaning' => [
                    'en' => 'funny',
                ],
            ],
            'message' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[3],
        ],
        'group' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'group' => 'dummy',
            ],
            'message' => [
                'text' => 'group: '.Adjective::VALIDATION_ERR_ENUM,
                'values' => Adjective::ALLOWED_GROUPS,
            ],
        ],
        'group_empty' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'group' => '',
            ],
            'message' => 'group: '.Adjective::VALIDATION_ERR_EMPTY,
        ],
        'group_invalid_hiragana' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'hiragana' => 'おもしろ',
                'group' => 'i',
            ],
            'message' => 'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[1],
        ],
        'group_invalid_kanji' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'kanji' => '面白',
                'group' => 'i',
            ],
            'message' => 'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[2],
        ],
        'jlpt_min' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'jlpt' => -1,
            ],
            'message' => 'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'fixture' => 'i_adjective',
            'payload' => [
                'jlpt' => 9000,
            ],
            'message' => 'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validAdjectiveProvider(): array
    {
        return [
            'hiragana' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                [
                    ...self::PUT_VALID_ADJECTIVES['hiragana'],
                    'kanji' => ' ', /* resetting kanji is required, otherwise
                    automatic conjugation will still use previous kanji
                    as a base */
                ],
                [
                    ...array_diff_key(
                        self::PUT_VALID_ADJECTIVES['hiragana'],
                        ['kanji' => null]
                    ),
                    'hiragana' => 'おかしい',
                    'inflections' => [
                        'non-past' => [
                            'affirmative' => 'おかしい',
                            'negative' => 'おかしくない',
                        ],
                        'past' => [
                            'affirmative' => 'おかしかった',
                            'negative' => 'おかしくなかった',
                        ],
                    ],
                ],
                'okashii',
            ],
            'katakana' => [
                self::PUT_FIXTURE_ADJECTIVES['na_adjective_katakana'],
                self::PUT_VALID_ADJECTIVES['katakana'],
                [
                    ...self::PUT_VALID_ADJECTIVES['katakana'],
                    'katakana' => 'オリジナル',
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
                'orijinaru',
            ],
            'romaji' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                self::PUT_VALID_ADJECTIVES['romaji'],
                [
                    ...self::PUT_VALID_ADJECTIVES['romaji'],
                    'romaji' => 'okashii',
                ],
                'okashii',
            ],
            'kanji' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                self::PUT_VALID_ADJECTIVES['kanji'],
                [
                    ...self::PUT_VALID_ADJECTIVES['kanji'],
                    'kanji' => '可笑しい',
                    'inflections' => [
                        'non-past' => [
                            'affirmative' => '可笑しい',
                            'negative' => '可笑しくない',
                        ],
                        'past' => [
                            'affirmative' => '可笑しかった',
                            'negative' => '可笑しくなかった',
                        ],
                    ],
                ],
                'okashii',
            ],
            'jlpt' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                self::PUT_VALID_ADJECTIVES['jlpt'],
                self::PUT_VALID_ADJECTIVES['jlpt'],
                'omoshiroi',
            ],
            'group' => [
                self::PUT_FIXTURE_ADJECTIVES['na_adjective'],
                self::PUT_VALID_ADJECTIVES['group'],
                [
                    ...self::PUT_VALID_ADJECTIVES['group'],
                    'hiragana' => 'なだかい',
                    'kanji' => '名高い',
                    'group' => 'i',
                    'inflections' => [
                        'non-past' => [
                            'affirmative' => '名高い',
                            'negative' => '名高くない',
                        ],
                        'past' => [
                            'affirmative' => '名高かった',
                            'negative' => '名高くなかった',
                        ],
                    ],
                ],
                'nadakai',
            ],
            'meaning' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                self::PUT_VALID_ADJECTIVES['meaning'],
                self::PUT_VALID_ADJECTIVES['meaning'],
                'omoshiroi',
            ],
            'meaning_new_lang' => [
                self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
                self::PUT_VALID_ADJECTIVES['meaning_new_lang'],
                self::PUT_VALID_ADJECTIVES['meaning_new_lang'],
                'omoshiroi',
            ],
        ];
    }

    /**
     * @dataProvider validAdjectiveProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testAdjectivesPutValid(
        array $fixture,
        array $payload,
        array $expected,
        string $code
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];
        $expectedIncrement = strstr(
            json_decode($response->getContent(), true)['code'],
            '-',
            true
        );

        // actual testing
        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Adjective::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidAdjectiveProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_ADJECTIVES,
            self::PUT_FIXTURE_ADJECTIVES
        );
    }

    /**
     * @dataProvider invalidAdjectiveProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testAdjectivesPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        // actual testing
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testAdjectivesPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/cards/adjectives/dummy',
            [
                'json' => self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAdjectivesPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives',
            [
                'json' => self::PUT_FIXTURE_ADJECTIVES['i_adjective'],
            ]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        static::createClient()->request(
            'PATCH',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => [
                    'romaji' => 'dame da yo',
                ],
            ],
        );
        $this->assertResponseStatusCodeSame(405);
    }
}
