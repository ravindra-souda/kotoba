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
        $this->assertEquals(Card::isValidHiragana($string), $expected);
    }

    /**
     * @return array<array<<string|bool>>
     */
    public function isValidKatakanaProvider(): array
    {
        return [
            ['', true],
            [null, true],
            ['ア', true],
            ['カ', true],
            ['ガ', true],
            ['キョ', true],
            ['Tシャツ', true],
            ['あ', false],
            ['a', false],
            ['1', false],
            ['字', false],
            ['アあ', false],
            ['あア', false],
            ['アあア', false],
            ['食ベル', false],
        ];
    }

    /**
     * @dataProvider isValidKatakanaProvider
     *
     * @param ?string $string
     * @param bool $expected
     */
    public function testIsValidKatakana(
        ?string $string,
        bool $expected,
    ): void {
        $this->assertEquals(Card::isValidKatakana($string), $expected);
    }

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
            ['あ', false],
            ['ア', false],
            ['a', false],
            ['1', false],
            ['食ベル', false],
            ['たべる', false],
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
        $this->assertEquals(Card::isValidKanji($string), $expected);
    }
}
