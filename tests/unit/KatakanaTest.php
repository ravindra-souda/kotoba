<?php

declare(strict_types=1);

use App\Document\Noun;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
class KatakanaTest extends TestCase
{
    /**
     * @return array<array<null|bool|string>>
     */
    public static function isValidKatakanaProvider(): array
    {
        return [
            'true_empty' => ['', true],
            'true_null' => [null, true],
            'true_katakana_vowel' => ['ア', true],
            'true_katakana_character' => ['カ', true],
            'true_katakana_dakuten' => ['ガ', true],
            'true_katakana_glide' => ['キョ', true],
            'true_Tシャツ' => ['Tシャツ', true],
            'false_hiragana' => ['あ', false],
            'false_romaji' => ['a', false],
            'false_integer' => ['1', false],
            'false_kanji' => ['字', false],
            'false_halfwidth_katakana' => ['ｦ', false],
            'false_katakana_and_hiragana' => ['アあ', false],
            'false_hiragana_and_katakana' => ['あア', false],
            'false_kanji_and_katakana' => ['食ベル', false],
        ];
    }

    /**
     * @param ?string $string
     */
    #[DataProvider('isValidKatakanaProvider')]
    public function testIsValidKatakana(
        ?string $string,
        bool $expected,
    ): void {
        $this->assertEquals($expected, Noun::isValidKatakana($string));
    }
}
