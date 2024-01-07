<?php

declare(strict_types=1);

use App\Document\Card;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CardTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function isValidMeaningProvider(): array
    {
        return [
            ['', true],
            [null, true],
            [
                [
                    'en' => 'to eat',
                ], 
                true
            ],
            [
                [
                    'en' => 'to eat',
                    'fr' => 'manger',
                ], 
                true
            ],
            [
                [
                    'fr' => 'manger',
                    'en' => 'to eat',
                ], 
                true
            ],
            [
                [
                    'fr' => 'manger',
                    'en' => 'to eat',
                    'dummy' => '???',
                ], 
                false
            ],
        ];
    }

    /**
     * @dataProvider isValidMeaningProvider
     *
     * @param string|array|null $meaning
     * @param bool $expected
     */
    public function testIsValidMeaning(
        string|array|null $meaning,
        bool $expected,
    ): void {
        $this->assertEquals(Card::isValidMeaning($meaning), $expected);
    }
}
