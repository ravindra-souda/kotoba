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
class NounsPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_FIXTURE_NOUNS = [
        'default' => [
            'hiragana' => 'なまえ',
            'katakana' => 'ナマエ',
            'kanji' => '名前',
            'romaji' => 'namae',
            'bikago' => 'お',
            'meaning' => [
                'en' => [
                    'name',
                    'given name, first name',
                ],
            ],
            'jlpt' => 5,
        ],
    ];

    private const PUT_VALID_NOUNS = [
        'hiragana' => [
            'hiragana' => '  じゅうしょ   ',
            'romaji' => '',
        ],
        'katakana' => [
            'katakana' => '   ジューショ',
            'hiragana' => '', /* has to be reset to test the romaji autofill
            because it will first look for hiragana and then katakana */
            'romaji' => '',
        ],
        'kanji' => [
            'kanji' => ' 住所   ',
        ],
        'romaji' => [
            'romaji' => '  juusho   ',
        ],
        'bikago' => [
            'hiragana' => '  ごじゅうしょ',
            'kanji' => 'ご住所  ',
            'romaji' => '  gojuusho  ',
            'bikago' => 'ご',
        ],
        'meaning' => [
            'meaning' => [
                'en' => [
                    '   address, residence, domicile ',
                ],
                'fr' => [
                    ' adresse, résidence, domicile ',
                ],
            ],
        ],
        'jlpt' => [
            'jlpt' => 4,
        ],
    ];

    private const PUT_EXPECTED_NOUNS = [
        'hiragana' => [
            'doc' => [
                'hiragana' => 'じゅうしょ',
                'romaji' => 'jūsho',
            ],
            'code' => 'jusho',
        ],
        'katakana' => [
            'doc' => [
                'katakana' => 'ジューショ',
                'hiragana' => null,
                'romaji' => 'jūsho',
            ],
            'code' => 'jusho',
        ],
        'kanji' => [
            'doc' => [
                'kanji' => '住所',
            ],
            'code' => 'namae',
        ],
        'romaji' => [
            'doc' => [
                'romaji' => 'jūsho',
            ],
            'code' => 'jusho',
        ],
        'bikago' => [
            'doc' => [
                'hiragana' => 'じゅうしょ',
                'kanji' => '住所',
                'romaji' => 'jūsho',
                'bikago' => 'ご',
            ],
            'code' => 'jusho',
        ],
        'meaning' => [
            'doc' => [
                'meaning' => [
                    'en' => [
                        'address, residence, domicile',
                    ],
                    'fr' => [
                        'adresse, résidence, domicile',
                    ],
                ],
            ],
            'code' => 'namae',
        ],
        'jlpt' => [
            'doc' => [
                'jlpt' => 4,
            ],
            'code' => 'namae',
        ],
    ];

    private const PUT_INVALID_NOUNS = [
        'hiragana' => [
            'fixture' => 'default',
            'payload' => [
                'hiragana' => '名前',
            ],
            'message' => 'hiragana: '.Noun::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'hiragana' => 'ひ',
            ],
            'message' => [
                'text' => 'hiragana: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana' => [
            'fixture' => 'default',
            'payload' => [
                'katakana' => 'namae',
            ],
            'message' => 'katakana: '.Noun::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_halfwidth' => [
            'fixture' => 'default',
            'payload' => [
                'katakana' => 'ﾅﾏｴ',
            ],
            'message' => 'katakana: '.Noun::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'katakana' => 'ツ',
            ],
            'message' => [
                'text' => 'katakana: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::KATAKANA_MAXLENGTH,
            ],
        ],
        'no_hiragana_nor_katakana' => [
            'fixture' => 'default',
            'payload' => [
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Noun::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'kanji' => [
            'fixture' => 'default',
            'payload' => [
                'kanji' => 'なまえ',
            ],
            'message' => 'kanji: '.Noun::VALIDATION_ERR_KANJI,
        ],
        'kanji_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'kanji' => '名',
            ],
            'message' => [
                'text' => 'kanji: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::KANJI_MAXLENGTH,
            ],
        ],
        'romaji' => [
            'fixture' => 'default',
            'payload' => [
                'romaji' => 'namaé',
            ],
            'message' => 'romaji: '.Noun::VALIDATION_ERR_ROMAJI,
        ],
        'romaji_maxlength' => [
            'fixture' => 'default',
            'maxlength' => [
                'romaji' => 'h',
            ],
            'message' => [
                'text' => 'romaji: '.Noun::VALIDATION_ERR_MAXLENGTH,
                'values' => Noun::ROMAJI_MAXLENGTH,
            ],
        ],
        'bikago' => [
            'fixture' => 'default',
            'payload' => [
                'bikago' => 'あ',
            ],
            'message' => [
                'text' => 'bikago: '.Noun::VALIDATION_ERR_ENUM,
                'values' => Noun::ALLOWED_BIKAGO,
            ],
        ],
        'meaning_missing_mandatory_lang' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'fr' => [
                        'nom',
                        'prénom',
                    ],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[1],
                'values' => Noun::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_unknown_lang' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'en' => ['name'],
                    'dummy' => ['nom'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[2],
                'values' => Noun::ALLOWED_LANGS,
            ],
        ],
        'meaning_type' => [
            'fixture' => 'default',
            'payload' => [
                'meaning' => [
                    'en' => [],
                ],
            ],
            'message' => 'meaning: '.Noun::VALIDATION_ERR_MEANING[3],
        ],
        'jlpt_min' => [
            'fixture' => 'default',
            'payload' => [
                'jlpt' => -5,
            ],
            'message' => 'jlpt: '.Noun::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'fixture' => 'default',
            'payload' => [
                'jlpt' => 50,
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

        foreach (self::PUT_VALID_NOUNS as $key => $payload) {
            $fixture = self::PUT_FIXTURE_NOUNS['default'];
            $payload = array_merge(
                $fixture,
                self::PUT_VALID_NOUNS[$key]
            );
            $expected = array_merge(
                $fixture,
                self::PUT_EXPECTED_NOUNS[$key]['doc']
            );
            $expected = array_filter($expected, fn ($val) => !is_null($val));
            $code = self::PUT_EXPECTED_NOUNS[$key]['code'];
            $provider[$key] = [$fixture, $payload, $expected, $code];
        }

        return $provider;
    }

    /**
     * @dataProvider validNounsProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testNounsPutValid(
        array $fixture,
        array $payload,
        array $expected,
        string $code,
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/nouns',
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
        $this->assertMatchesResourceItemJsonSchema(Noun::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidNounsProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_NOUNS,
            self::PUT_FIXTURE_NOUNS
        );
    }

    /**
     * @dataProvider invalidNounsProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testNounsPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/nouns',
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

    public function testNounsPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/cards/nouns/dummy',
            [
                'json' => self::PUT_VALID_NOUNS['hiragana'],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testNounsPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/nouns',
            [
                'json' => self::PUT_FIXTURE_NOUNS['default'],
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
