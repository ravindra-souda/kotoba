<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * @internal
 *
 * @coversNothing
 */
class KanjiDeleteTest extends KotobaApiTestCase
{
    private const DELETE_VALID_KANJI = [
        'kanji' => '猫',
        'meaning' => [
            'en' => ['cat'],
        ],
        'kunyomi' => ['neko'],
        'onyomi' => ['byou'],
    ];

    public function testKanjiDelete(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/kanji', [
                'headers' => ['Content-Type' => 'application/ld+json'],
                'json' => self::DELETE_VALID_KANJI
            ]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        // delete once and be happy
        static::createClient()->request(
            'DELETE',
            $_id,
        );
        $this->assertResponseStatusCodeSame(204);

        // delete twice and be sorry
        static::createClient()->request(
            'DELETE',
            $_id,
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
