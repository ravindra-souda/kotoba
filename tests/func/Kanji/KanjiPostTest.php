<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Kanji;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class KanjiPostTest extends ApiTestCase
{    
    private const POST_COMPLETE_VALID_KANJI = [
        'kanji' => [
            'kanji' => '人',
            'meaning' => [
                'en' => '    person ',
                'fr' => '  personne, humain    '
            ],
            'kunyomi' => '   hito, hitori, hitoto  ',
            'onyomi' => '  jin, nin  ',
        ],
    ];

    private const POST_COMPLETE_EXPECTED_KANJI = [
        'kanji' => [
            'kanji' => '人',
            'meaning' => [
                'en' => 'person',
                'fr' => 'personne, humain'
            ],
            'kunyomi' => 'ひと, ひとり, ひとと',
            'onyomi' => 'ジン, ニン',
        ],
    ];

    private const POST_MINIMAL_VALID_KANJI = [
        'kanji' => '犬',
        'meaning' => [
            'en' => 'dog',
        ],
        'kunyomi' => 'inu',
        'onyomi' => 'ken',
    ];

    private const POST_INVALID_KANJI = [
        'kanji_maxlength' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'kanji' => '犬猫',
        ],
        'kanji_written_in_romaji' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'kanji' => 'kanji',
        ],
        'meaning_empty' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'meaning' => [],
        ],
        'meaning_mandatory_lang_missing' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'meaning' => [
                'fr' => 'chien',
            ],
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'meaning' => [
                'en' => 'dog',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_KANJI,
            'jlpt' => 6,
        ],
        'kanji_kunyomi_empty' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'kunyomi' => '',
        ],
        'kanji_kunyomi_not_in_romaji' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'kunyomi' => 'ひと, ひとり, ひとと',
        ],
        'kanji_kunyomi_maxlength' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'kunyomi' => '*',
        ],
        'kanji_onyomi_empty' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'onyomi' => '',
        ],
        'kanji_onyomi_not_in_romaji' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'kunyomi' => 'ジン, ニン',
        ],
        'kanji_onyomi_maxlength' => [
            ...self::POST_COMPLETE_VALID_KANJI['kanji'],
            'onyomi' => '*',
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validKanjiProvider(): array
    {
        $provider = [];

        foreach(self::POST_COMPLETE_VALID_KANJI as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_KANJI[$key] ?? $value;
            $provider[] = [$value, $expected];
        }
        return $provider;
    }

    /**
     * @dataProvider validKanjiProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testKanjiPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Kanji::class);

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
    public function invalidKanjiProvider(): array
    {
        return [
            [
                self::POST_INVALID_KANJI['kanji_maxlength'],
                'kanji: '.Kanji::VALIDATION_ERR_KANJI
            ],
            [
                self::POST_INVALID_KANJI['kanji_written_in_romaji'],
                'kanji: '.Kanji::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_KANJI['meaning_empty'],
                'meaning: '.Kanji::VALIDATION_ERR_EMPTY,
            ],
            [
                self::POST_INVALID_KANJI['meaning_mandatory_lang_missing'],
                'meaning: '.Kanji::formatMsg(
                    Kanji::VALIDATION_ERR_MEANING[1], 
                    Kanji::getMandatoryLang(),
                )
            ],

            [
                self::POST_INVALID_KANJI['meaning_lang_unknown'],
                'meaning: '.Kanji::formatMsg(
                    Kanji::VALIDATION_ERR_MEANING[2], 
                    Kanji::getAllowedLangs()
                )
            ],
            [
                self::POST_INVALID_KANJI['jlpt_min'],
                'jlpt: '.Kanji::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_KANJI['jlpt_max'],
                'jlpt: '.Kanji::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_KANJI['kanji_kunyomi_empty'],
                'kunyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_KANJI['kanji_kunyomi_not_in_romaji'],
                'kunyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                [
                    ...self::POST_INVALID_KANJI['kanji_kunyomi_maxlength'],
                    'kunyomi' => str_repeat('a', Kanji::KUNYOMI_MAXLENGTH + 1),
                ],
                'kunyomi: '.Kanji::formatMsg(
                    Kanji::VALIDATION_ERR_MAXLENGTH, 
                    Kanji::KUNYOMI_MAXLENGTH
                ),
            ],
            [
                self::POST_INVALID_KANJI['kanji_onyomi_empty'],
                'onyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_KANJI['kanji_onyomi_not_in_romaji'],
                'onyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                [
                    ...self::POST_INVALID_KANJI['kanji_onyomi_maxlength'],
                    'onyomi' => str_repeat('a', Kanji::ONYOMI_MAXLENGTH + 1),
                ],
                'onyomi: '.Kanji::formatMsg(
                    Kanji::VALIDATION_ERR_MAXLENGTH, 
                    Kanji::ONYOMI_MAXLENGTH
                )
            ],
        ];
    }

    /**
     * @dataProvider invalidKanjiProvider
     *
     * @param array<string> $payload
     */
    public function testKanjiPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji',
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
