<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Kana;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class KanaPostTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const POST_COMPLETE_VALID_KANA = [
        'kana_hiragana' => [
            'romaji' => '  a',
            'hiragana' => '   あ ',
        ],
        'kana_katakana' => [
            'romaji' => '  a ',
            'katakana' => '   ア   ',
        ],
        'kana_hiragana_glide' => [
            'romaji' => '   kya ',
            'hiragana' => '   きゃ   ',
        ],
        'kana_katakana_glide' => [
            'romaji' => 'kya    ',
            'katakana' => '   キャ  ',
        ],
        'kana_hiragana_no_romaji' => [
            'hiragana' => '   う   ',
        ],
        'kana_katakana_no_romaji' => [
            'katakana' => '   ウ   ',
        ],
        'kana_hiragana_glide_no_romaji' => [
            'hiragana' => '     にょ   ',
        ],
        'kana_katakana_glide_no_romaji' => [
            'katakana' => ' シュ      ',
        ],
    ];

    private const POST_COMPLETE_EXPECTED_KANA = [
        'kana_hiragana_no_romaji' => [
            'romaji' => 'u',
            'hiragana' => 'う',
        ],
        'kana_katakana_no_romaji' => [
            'romaji' => 'u',
            'katakana' => 'ウ',
        ],
        'kana_hiragana_glide_no_romaji' => [
            'romaji' => 'nyo',
            'hiragana' => 'にょ',
        ],
        'kana_katakana_glide_no_romaji' => [
            'romaji' => 'shu',
            'katakana' => 'シュ',
        ],
    ];

    private const POST_MINIMAL_VALID_KANA = [
        'romaji' => 'ka',
        'hiragana' => 'か',
    ];

    private const POST_INVALID_KANA = [
        'romaji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_KANA,
            'maxlength' => [
                'romaji' => 'x',
            ],
            'message' => [
                'text' => 'romaji: '.Kana::VALIDATION_ERR_MAXLENGTH,
                'values' => Kana::ROMAJI_MAXLENGTH,
            ],
        ],
        'romaji_written_in_kana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'romaji' => 'ローマジ',
            ],
            'message' => 'romaji: '.Kana::VALIDATION_ERR_ROMAJI,
        ],
        'no_hiragana_nor_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_written_in_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'hiragana' => 'カタカナ',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_KANA,
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Kana::VALIDATION_ERR_MAXLENGTH,
                'values' => Kana::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'katakana' => 'ひらがな',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'katakana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_KANA,
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Kana::VALIDATION_ERR_MAXLENGTH,
                'values' => Kana::KATAKANA_MAXLENGTH,
            ],
        ],
        'jlpt_min' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'jlpt' => 0,
            ],
            'message' => 'jlpt: '.Kana::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANA,
                'jlpt' => 6,
            ],
            'message' => 'jlpt: '.Kana::VALIDATION_ERR_JLPT,
        ],
        'kana_hiragana' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANA['kana_hiragana'],
                'hiragana' => 'いい',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'kana_katakana' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANA['kana_katakana'],
                'katakana' => 'アア',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'kana_hiragana_glide' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANA['kana_hiragana_glide'],
                'hiragana' => 'きゃあ',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'kana_katakana_glide' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANA['kana_katakana_glide'],
                'katakana' => 'キャア',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validKanaProvider(): array
    {
        $provider = [];

        foreach (self::POST_COMPLETE_VALID_KANA as $key => $payload) {
            $expected = self::POST_COMPLETE_EXPECTED_KANA[$key] ?? $payload;
            $provider[$key] = [$payload, array_map('trim', $expected)];
        }

        return $provider;
    }

    /**
     * @dataProvider validKanaProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testKanaPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kana',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Kana::class);

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
    public function invalidKanaProvider(): array
    {
        return $this->buildPostProvider(self::POST_INVALID_KANA);
    }

    /**
     * @dataProvider invalidKanaProvider
     *
     * @param array<string> $payload
     */
    public function testKanaPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards/kana',
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
