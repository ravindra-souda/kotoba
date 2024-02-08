<?php

declare(strict_types=1);

use App\Document\Noun;
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
            'true_hiragana_glide' => ['きょ', true],
            'false_katakana' => ['ア', false],
            'false_romaji' => ['a', false],
            'false_integer' => ['1', false],
            'false_kanji' => ['字', false],
            'false_hiragana_and_romaji' => ['あ1', false],
            'false_katakana_and_hiragana' => ['アあ', false],
            'false_hiragana_and_katakana' => ['あアあ', false],
            'false_kanji_and_hiragana' => ['食べる', false],
        ];
    }

    /**
     * @dataProvider isValidHiraganaProvider
     *
     * @param ?string $string
     */
    public function testIsValidHiragana(?string $string, bool $expected): void
    {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(HiraganaTrait::class);
        $this->assertEquals($mock->isValidHiragana($string), $expected);
    }

    /**
     * @return array<array<null|bool|string>>
     */
    public function hasHiraganaOrKatakanaProvider(): array
    {
        return [
            'true_hiragana_katakana' => ['あ', 'ア', true],
            'true_hiragana_empty' => ['あ', '', true],
            'true_hiragana_space' => ['あ', ' ', true],
            'true_hiragana_null' => ['あ', null, true],
            'true_empty_katakana' => ['', 'ア', true],
            'true_space_katakana' => [' ', 'ア', true],
            'true_null_katakana' => [null, 'ア', true],
            'false_empty_empty' => ['', '', false],
            'false_space_empty' => [' ', '', false],
            'false_empty_space' => ['', ' ', false],
            'false_space_space' => [' ', ' ', false],
            'false_null_null' => [null, null, false],
            'false_empty_null' => ['', null, false],
            'false_space_null' => [' ', null, false],
            'false_null_empty' => [null, '', false],
            'false_null_space' => [null, ' ', false],
        ];
    }

    /**
     * @dataProvider hasHiraganaOrKatakanaProvider
     *
     * @param ?string $hiragana
     * @param ?string $katakana
     */
    public function testhasHiraganaOrKatakana(
        ?string $hiragana,
        ?string $katakana,
        bool $expected
    ): void {
        $noun = new Noun();
        $noun
            ->setHiragana($hiragana)
            ->setKatakana($katakana)
        ;

        $this->assertEquals($noun->hasHiraganaOrKatakana(), $expected);
    }

    /**
     * @return array<array<null|bool|string>>
     */
    public function hasHiraganaOrKatakanaSingleProvider(): array
    {
        return [
            'true' => ['あ', true],
            'false_empty' => ['', false],
            'false_space' => [' ', false],
            'false_null' => [null, false],
        ];
    }

    /*
     * @dataProvider hasHiraganaOrKatakanaSingleProvider
     *
     * @param ?string $hiragana
     * @param bool $expected
     */
    /* uncomment this when there will be an object with only hiragana
       and no katakana property
    public function testhasHiraganaOrKatakanaSingle(
        ?string $string,
        bool $expected
    ): void
    {
        $obj = new Obj();

        $obj->setHiragana($string);
        $this->assertEquals($obj->hasHiraganaOrKatakana(), $expected);
    }
    */
}
