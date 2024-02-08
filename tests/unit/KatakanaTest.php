<?php

declare(strict_types=1);

use App\Document\Trait\KatakanaTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class KatakanaTest extends TestCase
{
    /**
     * @return array<array<null|bool|string>>
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
            ['ｦ', false],
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
     */
    public function testIsValidKatakana(
        ?string $string,
        bool $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(KatakanaTrait::class);
        $this->assertEquals($mock->isValidKatakana($string), $expected);
    }
}
