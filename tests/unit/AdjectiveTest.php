<?php

declare(strict_types=1);

use App\Document\Adjective;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class AdjectiveTest extends TestCase
{
    /**
     * @return array<array<array<mixed>>>
     */
    public function conjugateValidProvider(): array
    {
        return [
            'i_adjective_with_kanji' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '  はやい  ',
                    'kanji' => ' 早い    ',
                ], [
                    'non-past' => [
                        'affirmative' => '早い',
                        'negative' => '早くない',
                    ],
                    'past' => [
                        'affirmative' => '早かった',
                        'negative' => '早くなかった',
                    ],
                ],
            ],
            'i_adjective_hiragana_only' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '       おそい ',
                    'kanji' => '    ',
                ], [
                    'non-past' => [
                        'affirmative' => 'おそい',
                        'negative' => 'おそくない',
                    ],
                    'past' => [
                        'affirmative' => 'おそかった',
                        'negative' => 'おそくなかった',
                    ],
                ],
            ],
            'i_adjective_exception' => [
                // i-adjective exception
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '  いい  ',
                ], [
                    'non-past' => [
                        'affirmative' => 'いい',
                        'negative' => 'よくない',
                    ],
                    'past' => [
                        'affirmative' => 'よかった',
                        'negative' => 'よくなかった',
                    ],
                ],
            ],
            'na_adjective_with_kanji' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => '       ゆうめい ',
                    'kanji' => '    有名',
                ], [
                    'non-past' => [
                        'affirmative' => '有名',
                        'negative' => '有名じゃない',
                    ],
                    'past' => [
                        'affirmative' => '有名でした',
                        'negative' => '有名じゃなかった',
                    ],
                ],
            ],
            'na_adjective_hiragana_only' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => '  きれい   ',
                    'kanji' => '  ',
                ], [
                    'non-past' => [
                        'affirmative' => 'きれい',
                        'negative' => 'きれいじゃない',
                    ],
                    'past' => [
                        'affirmative' => 'きれいでした',
                        'negative' => 'きれいじゃなかった',
                    ],
                ],
            ],
            'na_adjective_katakana_only' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => '    ',
                    'katakana' => '  ユニーク   ',
                    'kanji' => '  ',
                ], [
                    'non-past' => [
                        'affirmative' => 'ユニーク',
                        'negative' => 'ユニークじゃない',
                    ],
                    'past' => [
                        'affirmative' => 'ユニークでした',
                        'negative' => 'ユニークじゃなかった',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider conjugateValidProvider
     *
     * @param array<mixed> $params
     * @param array<mixed> $expected
     */
    public function testValidConjugate(
        array $params,
        array $expected,
    ): void {
        $adjective = new Adjective();
        $adjective
            ->setHiragana($params['hiragana'] ?? null)
            ->setKatakana($params['katakana'] ?? null)
            ->setKanji($params['kanji'] ?? null)
            ->setGroup($params['group'])
            ->conjugate()
        ;

        $this->assertEquals($adjective->getInflections(), $expected);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function conjugateInvalidProvider(): array
    {
        return [
            'incorrect_group_hiragana' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => 'にんき',
                ], Adjective::ERR_INCORRECT_GROUP,
            ],
            'incorrect_group_kanji' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'kanji' => '人気',
                ], Adjective::ERR_INCORRECT_GROUP,
            ],
            'no_base' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'kanji' => null,
                    'hiragana' => '',
                ], Adjective::ERR_NO_BASE,
            ],
        ];
    }

    /**
     * @dataProvider conjugateInvalidProvider
     *
     * @param array<string> $params
     */
    public function testInvalidConjugate(
        array $params,
        string $errMessage,
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($errMessage);

        $adjective = new Adjective();
        $adjective
            ->setHiragana($params['hiragana'] ?? null)
            ->setKanji($params['kanji'] ?? null)
            ->setGroup($params['group'])
            ->conjugate()
        ;
    }

    /**
     * @return array<array<array<string>|int>>
     */
    public function isValidGroupProvider(): array
    {
        return [
            '0_i_adjective_hiragana' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '  おいしい  ',
                ], 0,
            ],
            '0_i_adjective_kanji' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => 'いそがしい',
                    'kanji' => '   忙しい ',
                ], 0,
            ],
            '0_na_adjective_hiragana' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => ' きれい  ',
                ], 0,
            ],
            '0_na_adjective_kanji' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => ' げんき  ',
                    'kanji' => '  元気  ',
                ], 0,
            ],
            '0_na_adjective_katakana' => [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'katakana' => ' オリジナル  ',
                ], 0,
            ],
            '1_i_adjective_without_hiragana_い' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => ' しずか  ',
                ], 1,
            ],
            '1_i_adjective_with_kanji_い_without_hiragana_い' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'kanji' => '安い',
                    'hiragana' => ' やす  ', // spelling error
                ], 1,
            ],
            '1_i_adjective_katakana' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'katakana' => ' クレージー  ',
                ], 1,
            ],
            '2_i_adjective_without_kanji_い' => [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'kanji' => '高', // spelling error
                    'hiragana' => ' たかい  ',
                ], 2,
            ],
        ];
    }

    /**
     * @dataProvider isValidGroupProvider
     *
     * @param array<string> $params
     */
    public function testIsValidGroup(array $params, int $isValidGroup): void
    {
        $adjective = new Adjective();
        $adjective
            ->setHiragana($params['hiragana'] ?? null)
            ->setKatakana($params['katakana'] ?? null)
            ->setKanji($params['kanji'] ?? null)
            ->setGroup($params['group'])
        ;

        $this->assertEquals($adjective->isValidGroup(), $isValidGroup);
    }
}
