<?php

declare(strict_types=1);

use App\Document\Trait\KanjiTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class KanjiTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function isValidKanjiProvider(): array
    {
        return [
            ['', true],
            [null, true],
            ['字', true],
            ['食べる', true],
            ['お母さん', true],
            ['ご飯', true],
            ['あ', false],
            ['ア', false],
            ['a', false],
            ['1', false],
            ['食ベル', false],
            ['たべる', false],
            ['こ飯', false],
            ['おご飯', false],
            ['ごお飯', false],
        ];
    }

    /**
     * @dataProvider isValidKanjiProvider
     *
     * @param ?string $string
     * @param bool $expected
     */
    public function testIsValidKanji(
        ?string $string,
        bool $expected,
    ): void {
        $mock = $this->getMockForTrait(KanjiTrait::class);
        $this->assertEquals($mock->isValidKanji($string), $expected);
    }
}
