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
            ['あ', 'ア', true],
            ['あ', '', true],
            ['あ', ' ', true],
            ['あ', null, true],
            ['', 'ア', true],
            [' ', 'ア', true],
            [null, 'ア', true],
            ['', '', false],
            [' ', '', false],
            ['', ' ', false],
            [' ', ' ', false],
            [null, null, false],
            ['', null, false],
            [' ', null, false],
            [null, '', false],
            [null, ' ', false],
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
            ['あ', true],
            ['', false],
            [' ', false],
            [null, false],
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
