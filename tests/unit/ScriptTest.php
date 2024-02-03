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
            ['スーパーマリオブラザーズ', 'すうぱあまりおぶらざあず'],
            ['コーヒー', 'こうひい'],
            ['ケチャップ', 'けちゃっぷ'],
            ['ハッピー', 'はっぴい'],
            ['スーパーマーケット', 'すうぱあまあけっと'],
            ['コンビニ', 'こんびに'],
            ['ニュージーランド', 'にゅうじいらんど'],
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
            ['suupaa mario burazaazu', 'スーパーマリオブラザーズ'],
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

        /**
     * @return array<array<<string|bool>>
     */
    public function toRomajiProvider(): array
    {
        return [
            ['', null],
            [null, null],
            ['あ', 'a'],
            ['ア', 'a'],
            ['a', 'a'],
            ['だいがくせい', 'daigakusei'],
            ['がっこうきゅうしょく', 'gakkoukyuushoku'],
            ['スーパーマリオブラザーズ', 'suupaamarioburazaazu'],
            ['コーヒー', 'kouhii'],
            ['ケチャップ', 'kechappu'],
            ['ハッピー', 'happii'],
            ['スーパーマーケット', 'suupaamaaketto'],
            ['コンビニ', 'konbini'],
            ['ニュージーランド', 'nyuujiirando'],
            ['ひ、び、か', 'hi,bi,ka'],
            ['ニチ、ジツ', 'nichi,jitsu'],
            ['字', false],
            ['食べる', false],
            ['1', false],
            ['merii kurisumas', false],
        ];
    }

    /**
     * @dataProvider toRomajiProvider
     *
     * @param ?string $string
     * @param ?string\bool $expected
     */
    public function testToRomaji(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toRomaji($string), $expected);
    }
}
