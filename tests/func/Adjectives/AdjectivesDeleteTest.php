<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * @internal
 *
 * @coversNothing
 */
class AdjectivesDeleteTest extends KotobaApiTestCase
{
    private const DELETE_VALID_ADJECTIVE = [
        'hiragana' => 'おいしい',
        'group' => 'i',
        'meaning' => [
            'en' => ['delicious'],
        ],
    ];

    public function testAdjectivesDelete(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/adjectives', [
                'headers' => ['Content-Type' => 'application/ld+json'],
                'json' => self::DELETE_VALID_ADJECTIVE
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
