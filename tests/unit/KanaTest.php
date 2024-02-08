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
     * @return array<array<null|bool|string>>
     */
    public function isValidHiraganaProvider(): array
    {
        return [
            'true_empty' => ['', true],
            'true_null' => [null, true],
            'true_hiragana_vowel' => ['あ', true],
            'true_hiragana_character' => ['か', true],
            'true_hiragana_dakuten' => ['が', true],
            'true_hiragana_glide_ゃ' => ['にゃ', true],
            'true_hiragana_glide_ょ' => ['きょ', true],
            'true_hiragana_glide_ゅ' => ['りゅ', true],
            'true_chiisai_っ' => ['っこ', true],
            'false_katakana' => ['ア', false],
            'false_romaji' => ['a', false],
            'false_integer' => ['1', false],
            'false_kanji' => ['字', false],
            'false_hiragana_and_romaji' => ['あ1', false],
            'false_katakana_and_hiragana' => ['アあ', false],
            'false_hiragana_and_katakana' => ['あアあ', false],
            'false_kanji_and_hiragana' => ['食べる', false],
            'false_maxlength' => ['ねこ', false],
            'false_incorrect_glide_ぃ' => ['ぎぃ', false],
            'false_incorrect_glide_ぇ' => ['しぇ', false],
            'false_incorrect_glide_ょ' => ['かょ', false],
        ];
    }

    /**
     * @dataProvider isValidHiraganaProvider
     *
     * @param ?string $string
     */
    public function testIsValidHiragana(
        ?string $string,
        bool $expected,
    ): void {
        $this->assertEquals(Kana::isValidHiragana($string), $expected);
    }

    /**
     * @return array<array<null|bool|string>>
     */
    public function isValidKatakanaProvider(): array
    {
        return [
            'true_empty' => ['', true],
            'true_null' => [null, true],
            'true_katakana_vowel' => ['ア', true],
            'true_katakana_character' => ['カ', true],
            'true_katakana_dakuten' => ['ガ', true],
            'true_katakana_glide_ャ' => ['ニャ', true],
            'true_katakana_glide_ョ' => ['キョ', true],
            'true_katakana_glide_ュ' => ['リュ', true],
            'true_chiisai_ッ' => ['ッケ', true],
            'true_long' => ['チー', true],
            'false_Tシャツ' => ['Tシャツ', false],
            'false_hiragana' => ['あ', false],
            'false_romaji' => ['a', false],
            'false_integer' => ['1', false],
            'false_kanji' => ['字', false],
            'false_halfwidth_katakana' => ['ｦ', false],
            'false_katakana_and_hiragana' => ['アあ', false],
            'false_hiragana_and_katakana' => ['あア', false],
            'false_kanji_and_katakana' => ['食ベル', false],
            'false_incorrect_glide' => ['カィ', false],
            'false_incorrect_glide_dakuten' => ['ギィ', false],
        ];
    }

    /**
     * @return array<array<string|true>>
     */
    public function specialKatakanaGlideProvider(): array
    {
        // list from https://www.tofugu.com/japanese/learn-katakana/
        $specialGlides = [
            'ヴァ', 'ヴィ', 'ヴェ', 'ヴォ',
            'ウィ', 'ウェ', 'ウォ',
            'ファ', 'フィ', 'フェ', 'フォ',
            'ツァ', 'ツィ', 'ツェ', 'ツォ',
            'シェ',
            'ジェ',
            'チェ',
            'トゥ',
            'ティ',
            'ドゥ',
            'ディ',
        ];

        $provider = [];
        foreach ($specialGlides as $glide) {
            $provider['true_'.$glide] = [$glide, true];
        }

        return $provider;
    }

    /**
     * @dataProvider isValidKatakanaProvider
     * @dataProvider specialKatakanaGlideProvider
     *
     * @param ?string $string
     */
    public function testIsValidKatakana(
        ?string $string,
        bool $expected,
    ): void {
        $this->assertEquals(Kana::isValidKatakana($string), $expected);
    }
}
