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
class KanaPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_FIXTURE_KANA = [
        'hiragana' => [
            'hiragana' => 'ふ',
        ],
        'katakana' => [
            'katakana' => 'ジ',
        ],
    ];

    private const PUT_VALID_KANA = [
        'hiragana' => [
            'hiragana' => '   し  ',
        ],
        'katakana' => [
            'katakana' => '   ロ  ',
        ],
        'hiragana_and_romaji' => [
            'hiragana' => '   わ  ',
            'romaji' => '   wa',
        ],
        'katakana_and_romaji' => [
            'katakana' => ' ヨ    ',
            'romaji' => '  yo ',
        ],
        'hiragana_glide' => [
            'hiragana' => '   ぎゅ  ',
        ],
        'katakana_glide' => [
            'katakana' => '   リュ  ',
        ],
        'all' => [
            'hiragana' => '  りょ  ',
            'katakana' => ' リョ  ',
            'romaji' => '  ryo ',
        ],
    ];

    private const PUT_EXPECTED_KANA = [
        'hiragana' => [
            'hiragana' => 'し',
            'romaji' => 'shi',
        ],
        'katakana' => [
            'katakana' => 'ロ',
            'romaji' => 'ro',
        ],
        'hiragana_and_romaji' => [
            'hiragana' => 'わ',
            'romaji' => 'wa',
        ],
        'katakana_and_romaji' => [
            'katakana' => 'ヨ',
            'romaji' => 'yo',
        ],
        'hiragana_glide' => [
            'hiragana' => 'ぎゅ',
            'romaji' => 'gyu',
        ],
        'katakana_glide' => [
            'katakana' => 'リュ',
            'romaji' => 'ryu',
        ],
        'all' => [
            'hiragana' => 'りょ',
            'katakana' => 'リョ',
            'romaji' => 'ryo',
        ],
    ];

    private const PUT_INVALID_KANA = [
        'hiragana_written_in_katakana' => [
            'fixture' => 'hiragana',
            'payload' => [
                'hiragana' => 'コ',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'hiragana_written_in_romaji' => [
            'fixture' => 'hiragana',
            'payload' => [
                'hiragana' => 'ko',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'hiragana_empty' => [
            'fixture' => 'hiragana',
            'payload' => [
                'hiragana' => '',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_excluded' => [
            'fixture' => 'hiragana',
            'payload' => [
                'hiragana' => 'ゑ',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'fixture' => 'hiragana',
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Kana::VALIDATION_ERR_KANA_HIRAGANA,
                'values' => Kana::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'fixture' => 'katakana',
            'payload' => [
                'katakana' => 'こ',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'katakana_written_in_romaji' => [
            'fixture' => 'katakana',
            'payload' => [
                'katakana' => 'ko',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'katakana_empty' => [
            'fixture' => 'katakana',
            'payload' => [
                'katakana' => '',
            ],
            'message' => 'hiragana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'katakana_excluded' => [
            'fixture' => 'katakana',
            'payload' => [
                'katakana' => 'ヰ',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'katakana_half_width' => [
            'fixture' => 'katakana',
            'payload' => [
                'katakana' => 'ｺ',
            ],
            'message' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
        ],
        'katakana_maxlength' => [
            'fixture' => 'katakana',
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Kana::VALIDATION_ERR_KANA_KATAKANA,
                'values' => Kana::KATAKANA_MAXLENGTH,
            ],
        ],
        'romaji' => [
            'fixture' => 'hiragana',
            'payload' => [
                'romaji' => 'こ',
            ],
            'message' => 'romaji: '.Kana::VALIDATION_ERR_ROMAJI,
        ],
        'romaji_maxlength' => [
            'fixture' => 'hiragana',
            'maxlength' => [
                'romaji' => 'fu',
            ],
            'message' => [
                'text' => 'romaji: '.Kana::VALIDATION_ERR_MAXLENGTH,
                'values' => Kana::ROMAJI_MAXLENGTH,
            ],
        ],
        'no_hiragana_nor_katakana' => [
            'fixture' => 'hiragana',
            'payload' => [
                'hiragana' => '    ',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Kana::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validKanaProvider(): array
    {
        $provider = [];

        foreach (self::PUT_VALID_KANA as $key => $payload) {
            $fixture = str_starts_with($key, 'hiragana')
                ? self::PUT_FIXTURE_KANA['hiragana']
                : self::PUT_FIXTURE_KANA['katakana'];
            $expected = self::PUT_EXPECTED_KANA[$key];
            $provider[$key] = [$fixture, $payload, $expected];
        }

        return $provider;
    }

    /**
     * @dataProvider validKanaProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testKanaPutValid(
        array $fixture,
        array $payload,
        array $expected,
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kana',
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
        $this->assertMatchesResourceItemJsonSchema(Kana::class);

        $code = $expected['romaji'];
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidKanaProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_KANA,
            self::PUT_FIXTURE_KANA
        );
    }

    /**
     * @dataProvider invalidKanaProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testKanaPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kana',
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

    public function testKanaPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/cards/kana/dummy',
            [
                'json' => self::PUT_VALID_KANA['hiragana'],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testKanaPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kana',
            [
                'json' => self::PUT_FIXTURE_KANA['hiragana'],
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
                    'romaji' => 'fu',
                ],
            ],
        );
        $this->assertResponseStatusCodeSame(405);
    }
}
