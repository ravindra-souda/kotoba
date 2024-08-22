<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class VerbsDeleteTest extends ApiTestCase
{
    private const DELETE_VALID_VERB = [
        'hiragana' => 'おきる',
        'group' => 'ichidan',
        'inflections' => [
            'dictionary' => '起きる',
        ],
        'meaning' => [
            'en' => ['to wake up'],
        ],
    ];

    public function testVerbsDelete(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            ['json' => self::DELETE_VALID_VERB]
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
