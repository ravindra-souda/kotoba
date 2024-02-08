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
     * @return array<array<null|bool|string>>
     */
    public function isValidKanjiProvider(): array
    {
        return [
            'true_empty' => ['', true],
            'true_null' => [null, true],
            'true_kanji' => ['字', true],
            'true_kanji_with_okurigana' => ['食べる', true],
            'true_kanji_with_hiragana' => ['お母さん', true],
            'true_bikago_and_kanji' => ['ご飯', true],
            'false_hiragana' => ['あ', false],
            'false_katakana' => ['ア', false],
            'false_romaji' => ['a', false],
            'false_integer' => ['1', false],
            'false_kanji_and_katakana' => ['食ベル', false],
            'false_hiragana_verb' => ['たべる', false],
            'false_incorrect_bikago_and_kanji' => ['こ飯', false],
            'false_both_bikago_おご_and_kanji' => ['おご飯', false],
            'false_both_bikago_ごお_and_kanji' => ['ごお飯', false],
        ];
    }

    /**
     * @dataProvider isValidKanjiProvider
     *
     * @param ?string $string
     */
    public function testIsValidKanji(
        ?string $string,
        bool $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(KanjiTrait::class);
        $this->assertEquals($mock->isValidKanji($string), $expected);
    }
}
