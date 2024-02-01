<?php

declare(strict_types=1);

use App\Document\Trait\Script\ScriptTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ScriptTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function toHiraganaProvider(): array
    {
        return [
            ['', null],
            [null, null],
            ['a', 'あ'],
            ['ア', 'あ'],
            ['あ', 'あ'],
            ['chi', 'ち'],
            ['shi', 'し'],
            ['oishii', 'おいしい'],
            ['sensei', 'せんせい'],
            ['nihon', 'にほん'],
            ['gakkou', 'がっこう'],
            ['ohayou gozaimasu', 'おはようございます'],
            ['hi, bi, ka', 'ひ、び、か'],
            ['字', false],
            ['食べる', false],
            ['1', false],
            ['oisii', false],
            ['ohayou gozaimas', false],
        ];
    }

    /**
     * @dataProvider toHiraganaProvider
     *
     * @param ?string $string
     * @param ?string\bool $expected
     */
    public function testToHiragana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toHiragana($string), $expected);
    }

    
    /**
     * @return array<array<<string|bool>>
     */
    public function toKatakanaProvider(): array
    {
        return [
            ['', null],
            [null, null],
            ['a', 'ア'],
            ['ア', 'ア'],
            ['あ', 'ア'],
            ['chi', 'チ'],
            ['shi', 'シ'],
            ['pan', 'パン'],
            ['yuniiku', 'ユニーク'],
            ['merii kurisumasu', 'メリークリスマス'],
            ['nichi, jitsu', 'ニチ、ジツ'],
            ['字', false],
            ['食べる', false],
            ['1', false],
            ['si', false],
            ['merii kurisumas', false],
        ];
    }

    /**
     * @dataProvider toKatakanaProvider
     *
     * @param ?string $string
     * @param ?string\bool $expected
     */
    public function testToKatakana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toKatakana($string), $expected);
    }
}
