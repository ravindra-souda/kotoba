<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Noun;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class NounsPostTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const POST_COMPLETE_VALID_NOUNS = [
        'hiragana' => [
            'hiragana' => '   がっこう ',
            'kanji' => '学校',
            'jlpt' => 5,
            'meaning' => [
                'en' => [' schoOl'],
                'fr' => ['école '],
            ],
        ],
        'katakana' => [
            'katakana' => '    ネコ ',
            'kanji' => ' 猫  ',
            'jlpt' => 5,
            'meaning' => [
                'en' => [' cat  '],
                'fr' => [' cHat '],
            ],
        ],
        'bikago' => [
            'hiragana' => ' おかね',
            'kanji' => 'お金',
            'bikago' => 'お',
            'jlpt' => 5,
            'meaning' => [
                'en' => [' money   '],
            ],
        ],
        'romaji_filled' => [
            'romaji' => '    MonkeY ',
            'hiragana' => ' さる',
            'kanji' => '猿',
            'jlpt' => 3,
            'meaning' => [
                'en' => [' monkey   '],
            ],
        ],
        'romaji_long_wovel' => [
            'hiragana' => '  おおあめ ',
            'kanji' => '   大雨  ',
            'meaning' => [
                'en' => ['  heavy rain  '],
            ],
        ],
    ];

    private const POST_COMPLETE_EXPECTED_NOUNS = [
        'hiragana' => [
            ...self::POST_COMPLETE_VALID_NOUNS['hiragana'],
            'romaji' => 'gakkou',
            'hiragana' => 'がっこう',
            'meaning' => [
                'en' => ['school'],
                'fr' => ['école'],
            ],
        ],
        'katakana' => [
            ...self::POST_COMPLETE_VALID_NOUNS['katakana'],
            'romaji' => 'neko',
            'katakana' => 'ネコ',
            'kanji' => '猫',
            'meaning' => [
                'en' => ['cat'],
                'fr' => ['chat'],
            ],
        ],
        'bikago' => [
            ...self::POST_COMPLETE_VALID_NOUNS['bikago'],
            'romaji' => 'kane',
            'hiragana' => 'かね',
            'kanji' => '金',
            'meaning' => [
                'en' => ['money'],
            ],
        ],
        'romaji_filled' => [
            ...self::POST_COMPLETE_VALID_NOUNS['romaji_filled'],
            'romaji' => 'monkey',
            'hiragana' => 'さる',
            'meaning' => [
                'en' => ['monkey'],
            ],
        ],
        'romaji_long_wovel' => [
            ...self::POST_COMPLETE_VALID_NOUNS['romaji_long_wovel'],
            'romaji' => 'ōame',
            'hiragana' => 'おおあめ',
            'kanji' => '大雨',
            'meaning' => [
                'en' => ['heavy rain'],
            ],
        ],
    ];

    private const POST_MINIMAL_VALID_NOUN = [
        'romaji' => 'inu',
        'hiragana' => 'いぬ',
        'meaning' => [
            'en' => ['dog'],
        ],
    ];

    private const POST_INVALID_NOUNS = [
        'romaji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_NOUN,
            'maxlength' => [
                'romaji' => 'x',
            ],
            'message' => [
                'text' => 'romaji: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::ROMAJI_MAXLENGTH,
            ],
        ],
        'romaji_written_in_kana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'romaji' => 'ローマジ',
            ],
            'message' => 'romaji: '.Noun::VALIDATION_ERR_ROMAJI,
        ],
        'no_hiragana_nor_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_written_in_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'hiragana' => 'カタカナ',
            ],
            'message' => 'hiragana: '.Noun::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_NOUN,
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'katakana' => 'ひらがな',
            ],
            'message' => 'katakana: '.Noun::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_NOUN,
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::KATAKANA_MAXLENGTH,
            ],
        ],
        'kanji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_NOUN,
            'maxlength' => [
                'kanji' => '字',
            ],
            'message' => [
                'text' => 'kanji: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::KANJI_MAXLENGTH,
            ],
        ],
        'kanji_written_in_romaji' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'kanji' => 'kanji',
            ],
            'message' => 'kanji: '.Noun::VALIDATION_ERR_KANJI,
        ],
        'bikago' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_NOUNS['bikago'],
                'bikago' => 'dummy',
            ],
            'message' => [
                'text' => 'bikago: '.Noun::VALIDATION_ERR_ENUM,
                'values' => Noun::ALLOWED_BIKAGO,
            ],
        ],
        'meaning_mandatory_lang_missing' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'meaning' => [
                    'fr' => ['chien'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[1],
                'values' => Noun::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_lang_unknown' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'meaning' => [
                    'en' => ['dog'],
                    'dummy' => ['🂡🂱🃁🃑'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[2],
                'values' => Noun::ALLOWED_LANGS,
            ],
        ],
        'meaning_empty' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'meaning' => [
                    'en' => ['   '],
                ],
            ],
            'message' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[3],
        ],
        'meaning_invalid' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'meaning' => [
                    'en' => 'dog',
                ],
            ],
            'message' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[3],
        ],
        'jlpt_min' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'jlpt' => 0,
            ],
            'message' => 'jlpt: '.Noun::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_NOUN,
                'jlpt' => 6,
            ],
            'message' => 'jlpt: '.Noun::VALIDATION_ERR_JLPT,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validNounsProvider(): array
    {
        $provider = [];

        foreach (self::POST_COMPLETE_VALID_NOUNS as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_NOUNS[$key] ?? $value;
            $provider[$key] = [$value, $expected];
        }

        return $provider;
    }

    /**
     * @dataProvider validNounsProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testNounsPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/nouns',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Noun::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('createdAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['createdAt']);

        $expectedCode = str_replace(
            ['ā', 'ū', 'ē', 'ō'],
            ['a', 'u', 'e', 'o'],
            $expected['romaji']
        );
        $this->assertMatchesRegularExpression(
            '/\d+-'.$expectedCode.'/',
            $content['code']
        );
    }

    /**
     * @return array<array<array<mixed>>>
     */
    public function invalidNounsProvider(): array
    {
        return $this->buildPostProvider(self::POST_INVALID_NOUNS);
    }

    /**
     * @dataProvider invalidNounsProvider
     *
     * @param array<string> $payload
     */
    public function testNounsPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards/nouns',
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
