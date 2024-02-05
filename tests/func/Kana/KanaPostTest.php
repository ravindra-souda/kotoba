<?php

declare(strict_types=1);

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
            ...self::POST_MINIMAL_VALID_KANA,
            'romaji' => '*',
        ],
        'romaji_written_in_kana' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'romaji' => 'ローマジ',
        ],
        'no_hiragana_nor_katakana' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'hiragana' => '',
            'katakana' => '',
        ],
        'hiragana_written_in_katakana' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'hiragana' => 'カタカナ',
        ],
        'hiragana_maxlength' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'hiragana' => '*',
        ],
        'katakana_written_in_hiragana' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'katakana' => 'ひらがな',
        ],
        'katakana_maxlength' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'katakana' => '*',
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_KANA,
            'jlpt' => 6,
        ],
        'kana_hiragana' => [
            ...self::POST_COMPLETE_VALID_KANA['kana_hiragana'],
            'hiragana' => 'いい',
        ],
        'kana_katakana' => [
            ...self::POST_COMPLETE_VALID_KANA['kana_katakana'],
            'katakana' => 'アア',
        ],
        'kana_hiragana_glide' => [
            ...self::POST_COMPLETE_VALID_KANA['kana_hiragana_glide'],
            'hiragana' => 'きゃあ',
        ],
        'kana_katakana_glide' => [
            ...self::POST_COMPLETE_VALID_KANA['kana_katakana_glide'],
            'katakana' => 'キャア',
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validKanaProvider(): array
    {
        $provider = [];

        foreach(self::POST_COMPLETE_VALID_KANA as $key => $payload) {
            $expected = self::POST_COMPLETE_EXPECTED_KANA[$key] ?? $payload;
            $provider[] = [$payload, array_map('trim', $expected)];
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
        return [
            [
                [
                    ...self::POST_INVALID_KANA['romaji_maxlength'],
                    'romaji' => str_repeat('x', Kana::ROMAJI_MAXLENGTH + 1),
                ],
                'romaji: '.Kana::formatMsg(
                    Kana::VALIDATION_ERR_MAXLENGTH, 
                    Kana::ROMAJI_MAXLENGTH
                )
            ],
            [
                self::POST_INVALID_KANA['romaji_written_in_kana'],
                'romaji: '.Kana::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_KANA['no_hiragana_nor_katakana'],
                'hiragana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_KANA['no_hiragana_nor_katakana'],
                'katakana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_KANA['hiragana_written_in_katakana'],
                'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
            ],
            [
                [
                    ...self::POST_INVALID_KANA['hiragana_maxlength'],
                    'hiragana' => 
                        str_repeat('あ', Kana::HIRAGANA_MAXLENGTH + 1),
                ],
                'hiragana: '.Kana::formatMsg(
                    Kana::VALIDATION_ERR_MAXLENGTH, 
                    Kana::HIRAGANA_MAXLENGTH
                )
            ],
            [
                self::POST_INVALID_KANA['katakana_written_in_hiragana'],
                'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
            ],
            [
                [
                    ...self::POST_INVALID_KANA['katakana_maxlength'],
                    'katakana' => 
                        str_repeat('ア', Kana::KATAKANA_MAXLENGTH + 1),
                ],
                'katakana: '.Kana::formatMsg(
                    Kana::VALIDATION_ERR_MAXLENGTH, 
                    Kana::KATAKANA_MAXLENGTH
                )
            ],
            [
                self::POST_INVALID_KANA['jlpt_min'],
                'jlpt: '.Kana::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_KANA['jlpt_max'],
                'jlpt: '.Kana::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_KANA['kana_hiragana'],
                'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
            ],
            [
                self::POST_INVALID_KANA['kana_katakana'],
                'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
            ],
            [
                self::POST_INVALID_KANA['kana_hiragana_glide'],
                'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
            ],
            [
                self::POST_INVALID_KANA['kana_katakana_glide'],
                'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
            ],
        ];
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
