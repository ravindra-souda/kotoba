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
     * @return array<array<<string|bool>>
     */
    public function conjugateValidProvider(): array
    {
        return [
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '  はやい  ',
                    'kanji' => ' 早い    '
                ], [
                    'non-past' => [
                        'affirmative' => '早い',
                        'negative' => '早くない',
                    ],
                    'past' => [
                        'affirmative' => '早かった',
                        'negative' => '早くなかった',
                    ],
                ]
            ],
            [
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
                ]
            ],
            [
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
                ]
            ],
            [
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
                ]
            ],
            [
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
                ]
            ],
            [
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
                ]
            ],
        ];
    }

    /**
     * @dataProvider conjugateValidProvider
     *
     * @param array $params
     * @param array $expected
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
            ->conjugate();

        $this->assertEquals($adjective->getInflections(), $expected);
    }

        /**
     * @return array<array<<string|bool>>
     */
    public function conjugateInvalidProvider(): array
    {
        return [
                [
                    [
                        'group' => Adjective::I_ADJECTIVE,
                        'hiragana' => 'にんき',
                    ], Adjective::ERR_INCORRECT_GROUP
                ],
                [
                    [
                        'group' => Adjective::I_ADJECTIVE,
                        'kanji' => '人気',
                    ], Adjective::ERR_INCORRECT_GROUP
                ],
                [
                    [
                        'group' => Adjective::NA_ADJECTIVE,
                        'kanji' => null,
                        'hiragana' => '',
                    ], Adjective::ERR_NO_BASE
                ]
            ];
    }

    /**
     * @dataProvider conjugateInvalidProvider
     *
     * @param array $params
     * @param string $errMessage
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
            ->conjugate();
    }

    /**
     * @return array<array<<string|bool>>
     */
    public function isValidGroupProvider(): array
    {
        return [
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => '  おいしい  ',
                ], true
            ], 
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => 'いそがしい',
                    'kanji' => '   忙しい ',
                ], true
            ], 
            [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => ' きれい  ',
                ], true
            ],
            [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'hiragana' => ' げんき  ',
                    'kanji' => '  元気  ',
                ], true
            ],
            [
                [
                    'group' => Adjective::NA_ADJECTIVE,
                    'katakana' => ' オリジナル  ',
                ], true
            ],
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'hiragana' => ' しずか  ',
                ], false
            ],
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'kanji' => '高', // spelling error 
                    'hiragana' => ' たかい  ',
                ], false
            ],
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'kanji' => '安い',
                    'hiragana' => ' やす  ', // spelling error
                ], false
            ],
            [
                [
                    'group' => Adjective::I_ADJECTIVE,
                    'katakana' => ' クレージー  ',
                ], false
            ],
        ];
    }

    /**
     * @dataProvider isValidGroupProvider
     *
     * @param array $params
     * @param bool $expected
     */
    public function testIsValidGroup(
        array $params,
        bool $isValidGroup,
    ): void 
    {    
        $adjective = new Adjective();
        $adjective
            ->setHiragana($params['hiragana'] ?? null)
            ->setKatakana($params['katakana'] ?? null)
            ->setKanji($params['kanji'] ?? null)
            ->setGroup($params['group']);

        $this->assertEquals($adjective->isValidGroup(), $isValidGroup);
    }
}
