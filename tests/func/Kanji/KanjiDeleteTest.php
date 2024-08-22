<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class KanjiDeleteTest extends ApiTestCase
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
            '/api/cards/kanji',
            ['json' => self::DELETE_VALID_KANJI]
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
