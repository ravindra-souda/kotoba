<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DecksDeleteTest extends ApiTestCase
{
    private const DELETE_VALID_DECK = [
        'title' => 'deck to be deleted',
    ];

    public function testDecksDelete(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => self::DELETE_VALID_DECK]
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
