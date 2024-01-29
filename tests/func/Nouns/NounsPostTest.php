<?php

declare(strict_types=1);

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
    private const POST_COMPLETE_VALID_NOUNS = [
        'hiragana' => [
            'romaji' => '  gakKou       ',
            'hiragana' => '   がっこう ',
            'katakana' => '',
            'kanji' => '学校',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' schoOl',
                'fr' => 'école ',
            ],
        ],
        'katakana' => [
            'romaji' => '    Neko     ',
            'hiragana' => '',
            'katakana' => '    ネコ ',
            'kanji' => ' 猫  ',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' cat  ',
                'fr' => ' cHat ',
            ],
        ],
        'bikago' => [
            'romaji' => ' oKane ',
            'hiragana' => ' おかね',
            'katakana' => '',
            'kanji' => '金',
            'bikago' => 'お',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' money   ',
            ],
        ],
    ];

    private const POST_COMPLETE_EXPECTED_NOUNS = [
        'hiragana' => [
            ...self::POST_COMPLETE_VALID_NOUNS['hiragana'],
            'romaji' => 'gakkou',
            'hiragana' => 'がっこう',
            'meaning' => [
                'en' => 'school',
                'fr' => 'école',
            ],
        ],
        'katakana' => [
            ...self::POST_COMPLETE_VALID_NOUNS['katakana'],
            'romaji' => 'neko',
            'katakana' => 'ネコ',
            'kanji' => '猫',
            'meaning' => [
                'en' => 'cat',
                'fr' => 'chat',
            ],
        ],
        'bikago' => [
            ...self::POST_COMPLETE_VALID_NOUNS['bikago'],
            'romaji' => 'okane',
            'hiragana' => 'おかね',
            'meaning' => [
                'en' => 'money',
            ],
        ],
    ];

    private const POST_MINIMAL_VALID_NOUN = [
        'romaji' => 'inu',
        'hiragana' => 'いぬ',
        'meaning' => [
            'en' => 'dog',
        ]
    ];

    private const POST_INVALID_NOUNS = [
        'romaji_empty' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'romaji' => '',
        ],
        'romaji_maxlength' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'romaji' => '*',
        ],
        'romaji_written_in_kana' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'romaji' => 'ローマジ',
        ],
        'no_hiragana_nor_katakana' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'hiragana' => '',
            'katakana' => '',
        ],
        'hiragana_written_in_katakana' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'hiragana' => 'カタカナ',
        ],
        'hiragana_maxlength' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'hiragana' => '*',
        ],
        'katakana_written_in_hiragana' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'katakana' => 'ひらがな',
        ],
        'katakana_maxlength' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'katakana' => '*',
        ],
        'kanji_maxlength' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'kanji' => '*',
        ],
        'kanji_written_in_romaji' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'kanji' => 'kanji',
        ],
        'bikago' => [
            ...self::POST_COMPLETE_VALID_NOUNS['bikago'],
            'bikago' => 'dummy',
        ],
        'meaning_empty' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'meaning' => [],
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'meaning' => [
                'en' => 'dog',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'meaning_mandatory_lang_missing' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'meaning' => [
                'fr' => 'chien',
            ],
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_NOUN,
            'jlpt' => 6,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validNounsProvider(): array
    {
        $provider = [];

        foreach(self::POST_COMPLETE_VALID_NOUNS as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_NOUNS[$key] ?? $value;
            $provider[] = [$value, $expected];
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
        $this->assertMatchesRegularExpression(
            '/\d+-'.$expected['romaji'].'/',
            $content['code']
        );
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidNounsProvider(): array
    {
        return [
            [
                self::POST_INVALID_NOUNS['romaji_empty'],
                'romaji: '.Noun::VALIDATION_ERR_EMPTY,
            ],
            [
                [
                    ...self::POST_INVALID_NOUNS['romaji_maxlength'],
                    'romaji' => str_repeat('a', Noun::ROMAJI_MAXLENGTH + 1),
                ],
                'romaji: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MAXLENGTH, 
                    Noun::ROMAJI_MAXLENGTH
                )
            ],
            [
                self::POST_INVALID_NOUNS['romaji_written_in_kana'],
                'romaji: '.Noun::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_NOUNS['no_hiragana_nor_katakana'],
                'hiragana: '.Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_NOUNS['no_hiragana_nor_katakana'],
                'katakana: '.Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_NOUNS['hiragana_written_in_katakana'],
                'hiragana: '.Noun::VALIDATION_ERR_HIRAGANA,
            ],
            [
                [
                    ...self::POST_INVALID_NOUNS['hiragana_maxlength'],
                    'hiragana' => 
                        str_repeat('あ', Noun::HIRAGANA_MAXLENGTH + 1),
                ],
                'hiragana: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MAXLENGTH, 
                    Noun::HIRAGANA_MAXLENGTH
                ),
            ],
            [
                self::POST_INVALID_NOUNS['katakana_written_in_hiragana'],
                'katakana: '.Noun::VALIDATION_ERR_KATAKANA,
            ],
            [
                [
                    ...self::POST_INVALID_NOUNS['katakana_maxlength'],
                    'katakana' => 
                        str_repeat('ア', Noun::KATAKANA_MAXLENGTH + 1),
                ],
                'katakana: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MAXLENGTH, 
                    Noun::KATAKANA_MAXLENGTH
                )
            ],
            [
                [
                    ...self::POST_INVALID_NOUNS['kanji_maxlength'],
                    'kanji' => str_repeat('字', Noun::KANJI_MAXLENGTH + 1),
                ],
                'kanji: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MAXLENGTH,
                    Noun::KANJI_MAXLENGTH
                )
            ],
            [
                self::POST_INVALID_NOUNS['kanji_written_in_romaji'],
                'kanji: '.Noun::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_NOUNS['bikago'],
                'bikago: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_ENUM,
                    Noun::ALLOWED_BIKAGO,
                )
            ],
            [
                self::POST_INVALID_NOUNS['meaning_empty'],
                'meaning: '.Noun::VALIDATION_ERR_EMPTY,
            ],
            [
                self::POST_INVALID_NOUNS['meaning_lang_unknown'],
                'meaning: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MEANING[1],
                    Noun::getAllowedLangs(),
                )
            ],
            [
                self::POST_INVALID_NOUNS['meaning_mandatory_lang_missing'],
                'meaning: '.Noun::formatMsg(
                    Noun::VALIDATION_ERR_MEANING[2], 
                    Noun::getMandatoryLang(),
                )
            ],
            [
                self::POST_INVALID_NOUNS['jlpt_min'],
                'jlpt: '.Noun::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_NOUNS['jlpt_max'],
                'jlpt: '.Noun::VALIDATION_ERR_JLPT,
            ],
        ];
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
