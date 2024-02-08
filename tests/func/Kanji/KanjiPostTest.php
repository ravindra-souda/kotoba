<?php

declare(strict_types=1);

namespace App\Tests;

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
    use Trait\BuildProviderTrait;

    private const POST_COMPLETE_VALID_KANJI = [
        'kanji' => [
            'kanji' => '人',
            'meaning' => [
                'en' => ['    person, human '],
                'fr' => ['  personne, humain    '],
            ],
            'kunyomi' => '   hito, hitori, hitoto  ',
            'onyomi' => '  jin, nin  ',
        ],
        'long_wovel' => [
            'kanji' => '週',
            'meaning' => [
                'en' => ['    week '],
            ],
            'onyomi' => '  shū  ',
        ],
        'kokuji_without_onyomi' => [
            'kanji' => '込',
            'meaning' => [
                'en' => ['    crowded, mixture, in bulk, included '],
            ],
            'kunyomi' => '  ko  ',
        ],
    ];

    private const POST_COMPLETE_EXPECTED_KANJI = [
        'kanji' => [
            [
                'kanji' => '人',
                'meaning' => [
                    'en' => ['person, human'],
                    'fr' => ['personne, humain'],
                ],
                'kunyomi' => 'ひと、ひとり、ひとと',
                'onyomi' => 'ジン、ニン',
            ], 'person',
        ],
        'long_wovel' => [
            [
                'kanji' => '週',
                'meaning' => [
                    'en' => ['week'],
                ],
                'onyomi' => 'シュウ',
            ], 'week',
        ],
        'kokuji_without_onyomi' => [
            [
                'kanji' => '込',
                'meaning' => [
                    'en' => ['crowded, mixture, in bulk, included'],
                ],
                'kunyomi' => 'こ',
            ], 'crowded',
        ],
    ];

    private const POST_MINIMAL_VALID_KANJI = [
        'kanji' => '犬',
        'meaning' => [
            'en' => ['dog'],
        ],
        'kunyomi' => 'inu',
        'onyomi' => 'ken',
    ];

    private const POST_INVALID_KANJI = [
        'kanji_maxlength' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'kanji' => '犬猫',
            ],
            'message' => 'kanji: '.Kanji::VALIDATION_ERR_KANJI,
        ],
        'kanji_written_in_romaji' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'kanji' => 'kanji',
            ],
            'message' => 'kanji: '.Kanji::VALIDATION_ERR_KANJI,
        ],
        'meaning_mandatory_lang_missing' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'meaning' => [
                    'fr' => ['chien'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[1],
                'values' => Kanji::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_lang_unknown' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'meaning' => [
                    'en' => ['dog'],
                    'dummy' => ['🂡🂱🃁🃑'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[2],
                'values' => Kanji::ALLOWED_LANGS,
            ],
        ],
        'meaning_empty' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'meaning' => [
                    'en' => [''],
                ],
            ],
            'message' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[3],
        ],
        'meaning_invalid' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'meaning' => [
                    'en' => 'dog',
                ],
            ],
            'message' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[3],
        ],
        'jlpt_min' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'jlpt' => 0,
            ],
            'message' => 'jlpt: '.Kanji::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'jlpt' => 6,
            ],
            'message' => 'jlpt: '.Kanji::VALIDATION_ERR_JLPT,
        ],
        'kanji_kunyomi_onyomi_empty' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_KANJI,
                'kunyomi' => '',
                'onyomi' => '',
            ],
            'message' => 'kunyomi: '.
                Kanji::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI.
                PHP_EOL.
                'onyomi: '.
                Kanji::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI,
        ],
        'kanji_kunyomi_not_in_romaji' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANJI['kanji'],
                'kunyomi' => 'ひと, ひとり, ひとと',
            ],
            'message' => 'kunyomi: '.Kanji::VALIDATION_ERR_KUNYOMI,
        ],
        'kanji_kunyomi_maxlength' => [
            'payload' => self::POST_COMPLETE_VALID_KANJI['kanji'],
            'maxlength' => [
                'kunyomi' => 'a',
            ],
            'message' => [
                'text' => 'kunyomi: '.Kanji::VALIDATION_ERR_MAXLENGTH,
                'values' => Kanji::KUNYOMI_MAXLENGTH,
            ],
        ],
        'kanji_onyomi_not_in_romaji' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_KANJI['kanji'],
                'onyomi' => 'ジン, ニン',
            ],
            'message' => 'onyomi: '.Kanji::VALIDATION_ERR_ONYOMI,
        ],
        'kanji_onyomi_maxlength' => [
            'payload' => self::POST_COMPLETE_VALID_KANJI['kanji'],
            'maxlength' => [
                'onyomi' => 'a',
            ],
            'message' => [
                'text' => 'onyomi: '.Kanji::VALIDATION_ERR_MAXLENGTH,
                'values' => Kanji::ONYOMI_MAXLENGTH,
            ],
        ],
    ];

    /**
     * @return array<array<array<mixed>>>
     */
    public function validKanjiProvider(): array
    {
        $provider = [];

        foreach (self::POST_COMPLETE_VALID_KANJI as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_KANJI[$key] ?? $value;
            $provider[$key] = [$value, ...$expected];
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
        string $code,
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
            '/\d+-'.$code.'/',
            $content['code']
        );
    }

    /**
     * @return array<array<mixed>>
     */
    public function invalidKanjiProvider(): array
    {
        return $this->buildPostProvider(self::POST_INVALID_KANJI);
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
