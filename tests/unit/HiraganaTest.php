<?php

declare(strict_types=1);

use App\Document\Trait\HiraganaTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class HiraganaTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function isValidHiraganaProvider(): array
    {
        return [
            ['', true],
            [null, true],
            ['あ', true],
            ['か', true],
            ['が', true],
            ['きょ', true],
            ['ア', false],
            ['a', false],
            ['1', false],
            ['字', false],
            ['あ1', false],
            ['アあ', false],
            ['あアあ', false],
            ['食べる', false],
        ];
    }

    /**
     * @dataProvider isValidHiraganaProvider
     *
     * @param ?string $string
     * @param bool $expected
     */
    public function testIsValidHiragana(
        ?string $string,
        bool $expected,
    ): void {
        $mock = $this->getMockForTrait(HiraganaTrait::class);
        $this->assertEquals($mock->isValidHiragana($string), $expected);
    }
}
