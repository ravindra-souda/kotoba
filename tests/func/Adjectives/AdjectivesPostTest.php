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
class AdjectivesPostTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const POST_COMPLETE_VALID_ADJECTIVES = [
        'i_adjective' => [
            'hiragana' => 'かわいい',
            'kanji' => '可愛い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => ['cute, adorable, charming, lovely, pretty'],
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
            'en' => ['delicious'],
        ],
    ];

    private const POST_INVALID_ADJECTIVES = [
        'romaji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_ADJECTIVE,
            'maxlength' => [
                'romaji' => 'x',
            ],
            'message' => [
                'text' => 'romaji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::ROMAJI_MAXLENGTH,
            ],
        ],
        'romaji_written_in_kana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'romaji' => 'ローマジ',
            ],
            'message' => 'romaji: '.Adjective::VALIDATION_ERR_ROMAJI,
        ],
        'no_hiragana_nor_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Adjective::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_written_in_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'hiragana' => 'カタカナ',
            ],
            'message' => 'hiragana: '.Adjective::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_ADJECTIVE,
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'katakana' => 'ひらがな',
            ],
            'message' => 'katakana: '.Adjective::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_ADJECTIVE,
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::KATAKANA_MAXLENGTH,
            ],
        ],
        'kanji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_ADJECTIVE,
            'maxlength' => [
                'kanji' => '字',
            ],
            'message' => [
                'text' => 'kanji: '.Adjective::VALIDATION_ERR_MAXLENGTH,
                'values' => Adjective::KANJI_MAXLENGTH,
            ],
        ],
        'kanji_written_in_romaji' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'kanji' => 'kanji',
            ],
            'message' => 'kanji: '.Adjective::VALIDATION_ERR_KANJI,
        ],
        'meaning_mandatory_lang_missing' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'meaning' => [
                    'fr' => ['delicieux'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[1],
                'values' => Adjective::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_lang_unknown' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'meaning' => [
                    'en' => ['delicious'],
                    'dummy' => ['🂡🂱🃁🃑'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[2],
                'values' => Adjective::ALLOWED_LANGS,
            ],
        ],
        'meaning_empty' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'meaning' => [
                    'en' => [' '],
                ],
            ],
            'message' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[3],
        ],
        'meaning_invalid' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'meaning' => [
                    'en' => 'delicious',
                ],
            ],
            'message' => 'meaning: '.Adjective::VALIDATION_ERR_MEANING[3],
        ],
        'group_adjective' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
                'group' => 'dummy',
            ],
            'message' => [
                'text' => 'group: '.Adjective::VALIDATION_ERR_ENUM,
                'values' => Adjective::ALLOWED_GROUPS,
            ],
        ],
        'group_invalid_hiragana' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
                'hiragana' => 'すてき',
            ],
            'message' => 'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[1],
        ],
        'group_invalid_kanji' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_ADJECTIVES['i_adjective'],
                'kanji' => '素敵',
            ],
            'message' => 'group: '.Adjective::VALIDATION_ERR_I_ADJECTIVE[2],
        ],
        'jlpt_min' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'jlpt' => 0,
            ],
            'message' => 'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_ADJECTIVE,
                'jlpt' => 6,
            ],
            'message' => 'jlpt: '.Adjective::VALIDATION_ERR_JLPT,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validAdjectivesProvider(): array
    {
        $provider = [];

        foreach (self::POST_COMPLETE_VALID_ADJECTIVES as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_ADJECTIVES[$key] ?? $value;
            $provider[$key] = [$value, $expected];
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
     * @return array<array<array<mixed>>>
     */
    public function invalidAdjectivesProvider(): array
    {
        return $this->buildPostProvider(self::POST_INVALID_ADJECTIVES);
    }

    /**
     * @dataProvider invalidAdjectivesProvider
     *
     * @param array<string> $payload
     */
    public function testAdjectivesPostInvalid(
        array $payload,
        string $message
    ): void {
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
