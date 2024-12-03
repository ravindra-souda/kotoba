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
class KanjiPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_FIXTURE_KANJI = [
        'default' => [
            'kanji' => '日',
            'meaning' => [
                'en' => [
                    'day, daytime, daylight',
                    'sun, sunshine, sunlight',
                ],
                'fr' => [
                    'jour, lumière du jour',
                    'soleil',
                ],
            ],
            'kunyomi' => 'hi, bi, ka',
            'onyomi' => 'nichi, jitsu',
        ],
    ];

    private const PUT_VALID_KANJI = [
        'kanji' => [
            'kanji' => '   月  ',
        ],
        'meaning' => [
            'meaning' => [
                'en' => [
                    '    Moon ',
                    ' month',
                    'moonlight ',
                ],
                'fr' => [
                    ' Lune',
                    '    mois',
                    'clair de lune   ',
                ],
            ],
        ],
        'kunyomi' => [
            'kunyomi' => ' tsuki  ',
        ],
        'onyomi' => [
            'onyomi' => ' getsu, gatsu    ',
        ],
    ];

    private const PUT_EXPECTED_KANJI = [
        'kanji' => [
            'doc' => [
                'kanji' => '月',
                'kunyomi' => 'ひ、び、か',
                'onyomi' => 'ニチ、ジツ',
            ],
            'code' => 'day',
        ],
        'meaning' => [
            'doc' => [
                'meaning' => [
                    'en' => [
                        'moon',
                        'month',
                        'moonlight',
                    ],
                    'fr' => [
                        'lune',
                        'mois',
                        'clair de lune',
                    ],
                ],
                'kunyomi' => 'ひ、び、か',
                'onyomi' => 'ニチ、ジツ',
            ],
            'code' => 'moon',
        ],
        'kunyomi' => [
            'doc' => [
                'kunyomi' => 'つき',
                'onyomi' => 'ニチ、ジツ',
            ],
            'code' => 'day',
        ],
        'onyomi' => [
            'doc' => [
                'onyomi' => 'ゲツ、ガツ',
                'kunyomi' => 'ひ、び、か',
            ],
            'code' => 'day',
        ],
    ];

    private const PUT_INVALID_KANJI = [
        'kanji_not_written_in_kanji' => [
            'fixture' => 'default',
            'payload' => [
                'kanji' => 'の',
            ],
            'message' => 'kanji: '.Kanji::VALIDATION_ERR_KANJI,
        ],
        'kanji_maxlength' => [
            'fixture' => 'default',
            'payload' => [
                'kanji' => '日本',
            ],
            'message' => 'kanji: '.Kanji::VALIDATION_ERR_KANJI,
        ],
        'meaning_missing_mandatory_lang' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'fr' => [
                        'Lune',
                        'mois',
                        'clair de lune',
                    ],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[1],
                'values' => Kanji::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_unknown_lang' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'en' => [
                        'Moon',
                        'month',
                        'moonlight',
                    ],
                    'dummy' => [
                        'Lune',
                        'mois',
                        'clair de lune',
                    ],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[2],
                'values' => Kanji::ALLOWED_LANGS,
            ],
        ],
        'meaning_type' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'en' => 'Moon',
                ],
            ],
            'message' => 'meaning: '.Kanji::VALIDATION_ERR_MEANING[3],
        ],
        'kunyomi_not_written_in_romaji' => [
            'fixture' => 'default',
            'payload' => [
                'kunyomi' => 'つき',
            ],
            'message' => 'kunyomi: '.Kanji::VALIDATION_ERR_KUNYOMI,
        ],
        'kunyomi_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'kunyomi' => 'h',
            ],
            'message' => [
                'text' => 'kunyomi: '.Kanji::VALIDATION_ERR_MAXLENGTH,
                'values' => Kanji::KUNYOMI_MAXLENGTH,
            ],
        ],
        'onyomi_not_written_in_romaji' => [
            'fixture' => 'default',
            'payload' => [
                'onyomi' => 'ゲツ',
            ],
            'message' => 'onyomi: '.Kanji::VALIDATION_ERR_ONYOMI,
        ],
        'onyomi_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'onyomi' => 'k',
            ],
            'message' => [
                'text' => 'onyomi: '.Kanji::VALIDATION_ERR_MAXLENGTH,
                'values' => Kanji::ONYOMI_MAXLENGTH,
            ],
        ],
        'no_kunyomi_nor_onyomi' => [
            'fixture' => 'default',
            'payload' => [
                'kunyomi' => '',
                'onyomi' => '     ',
            ],
            'message' => 'kunyomi: '.
                Kanji::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI.
                PHP_EOL.
                'onyomi: '.
                Kanji::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validKanjiProvider(): array
    {
        $provider = [];

        foreach (self::PUT_VALID_KANJI as $key => $payload) {
            $fixture = self::PUT_FIXTURE_KANJI['default'];
            $payload = array_merge(
                $fixture,
                self::PUT_VALID_KANJI[$key]
            );
            $expected = array_merge(
                $fixture,
                self::PUT_EXPECTED_KANJI[$key]['doc']
            );
            $code = self::PUT_EXPECTED_KANJI[$key]['code'];
            $provider[$key] = [$fixture, $payload, $expected, $code];
        }

        return $provider;
    }

    /**
     * @dataProvider validKanjiProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testKanjiPutValid(
        array $fixture,
        array $payload,
        array $expected,
        string $code,
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji',
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
        $this->assertMatchesResourceItemJsonSchema(Kanji::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidKanjiProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_KANJI,
            self::PUT_FIXTURE_KANJI
        );
    }

    /**
     * @dataProvider invalidKanjiProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testKanjiPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji',
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

    public function testKanjiPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/cards/kanji/dummy',
            [
                'json' => self::PUT_VALID_KANJI['kanji'],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testKanjiPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji',
            [
                'json' => self::PUT_FIXTURE_KANJI['default'],
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
                    'kunyomi' => 'tsuki',
                ],
            ],
        );
        $this->assertResponseStatusCodeSame(405);
    }
}
