<?php

declare(strict_types=1);

use App\Document\Kana;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class KanaTest extends TestCase
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
            ['にゃ', true],
            ['きょ', true],
            ['りゅ', true],
            ['っこ', true],
            ['ア', false],
            ['a', false],
            ['1', false],
            ['字', false],
            ['あ1', false],
            ['アあ', false],
            ['あアあ', false],
            ['食べる', false],
            ['ねこ', false],
            ['ぎぃ', false],
            ['しぇ', false],
            ['かょ', false],
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
        $this->assertEquals(Kana::isValidHiragana($string), $expected);
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
            ['ニャ', true],
            ['キョ', true],
            ['リュ', true],
            ['ッケ', true],
            ['チー', true],
            ['Tシャツ', false],
            ['あ', false],
            ['a', false],
            ['1', false],
            ['字', false],
            ['ｦ', false],
            ['アあ', false],
            ['あア', false],
            ['アあア', false],
            ['食ベル', false],
            ['ギィ', false],
            ['カィ', false],
        ];
    }

    /**
     * @return array<array<<string|bool>>
     */
    public function specialKatakanaGlideProvider(): array
    {
        // list from https://www.tofugu.com/japanese/learn-katakana/
        return [
            ['ヴァ', true],
            ['ヴィ', true],
            ['ヴェ', true],
            ['ヴォ', true],
            ['ウィ', true],
            ['ウェ', true],
            ['ウォ', true],
            ['ファ', true],
            ['フィ', true],
            ['フェ', true],
            ['フォ', true],
            ['ツァ', true],
            ['ツィ', true],
            ['ツェ', true],
            ['ツォ', true],
            ['シェ', true],
            ['ジェ', true],
            ['チェ', true],
            ['トゥ', true],
            ['ティ', true],
            ['ドゥ', true],
            ['ディ', true],
        ];
    }

    /**
     * @dataProvider isValidKatakanaProvider
     * @dataProvider specialKatakanaGlideProvider
     *
     * @param ?string $string
     * @param bool $expected
     */
    public function testIsValidKatakana(
        ?string $string,
        bool $expected,
    ): void {
        $this->assertEquals(Kana::isValidKatakana($string), $expected);
    }
}
