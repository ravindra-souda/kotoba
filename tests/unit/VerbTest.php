<?php

declare(strict_types=1);

use App\Document\Verb;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class VerbTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function isValidInflectionsProvider(): array
    {
        return [
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '食べる',
                    ],
                ], true
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '飲む',
                    ],
                ], true
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => null,
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => '',
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dict' => '食べる',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'taberu',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '食う',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '食物',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'nomu',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'する',
                    ],
                ], false
            ],[
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'する',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '来る',
                    ],
                ], false
            ],[
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '来る',
                    ],
                ], false
            ],
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'くる',
                    ],
                ], false
            ],[
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'くる',
                    ],
                ], false
            ],
        ];
    }

    /**
     * @dataProvider isValidInflectionsProvider
     *
     * @param array $params
     * @param bool $expected
     */
    public function testIsValidInflections(
        array $params,
        bool $expected,
    ): void {
        $verb = new Verb();
        $verb->setGroup($params['group']);

        $this->assertEquals($verb->isValidInflections(
            $params['inflections']), $expected
        );
    }

    /**
     * @return array<array<<string|bool>>
     */
    public function conjugateValidProvider(): array
    {
        return [
            [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '     見る ',
                    ],
                ], [
                    'dictionary' => '見る',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => '見る',
                            'negative' => '見ない',
                        ],
                        'polite' => [
                            'affirmative' => '見ます',
                            'negative' => '見ません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => '見た', 
                            'negative' => '見なかった',
                        ],
                        'polite' => [
                            'affirmative' => '見ました',
                            'negative' => '見ませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => '見て',
                        'negative' => '見なくて',
                    ],
                    'potential' => [
                        'affirmative' => '見られる',
                        'negative' => '見られない',
                    ],
                    'passive' => [
                        'affirmative' => '見られる',
                        'negative' => '見られない',
                    ],
                    'causative' => [
                        'affirmative' => '見させる',
                        'negative' => '見させない',
                        'passive' => [
                            'affirmative' => '見させられる',
                            'negative' => '見させられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => '見ろ',
                        'negative' => '見るな',
                    ],
                ],
                false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '       聞く    ',
                    ],
                ], [
                    'dictionary' => '聞く',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => '聞く',
                            'negative' => '聞かない',
                        ],
                        'polite' => [
                            'affirmative' => '聞きます',
                            'negative' => '聞きません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => '聞いた', 
                            'negative' => '聞かなかった',
                        ],
                        'polite' => [
                            'affirmative' => '聞きました',
                            'negative' => '聞きませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => '聞いて',
                        'negative' => '聞かなくて',
                    ],
                    'potential' => [
                        'affirmative' => '聞ける',
                        'negative' => '聞けない',
                    ],
                    'passive' => [
                        'affirmative' => '聞かれる',
                        'negative' => '聞かれない',
                    ],
                    'causative' => [
                        'affirmative' => '聞かせる',
                        'negative' => '聞かせない',
                        'passive' => [
                            'affirmative' => '聞かせられる',
                            'negative' => '聞かせられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => '聞け',
                        'negative' => '聞くな',
                    ],
                ],
                false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '  買う      ',
                    ],
                ], [
                    'dictionary' => '買う',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => '買う',
                            'negative' => '買わない', // u-column special rule
                        ],
                        'polite' => [
                            'affirmative' => '買います',
                            'negative' => '買いません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => '買った',
                            'negative' => '買わなかった',
                        ],
                        'polite' => [
                            'affirmative' => '買いました',
                            'negative' => '買いませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => '買って',
                        'negative' => '買わなくて',
                    ],
                    'potential' => [
                        'affirmative' => '買える',
                        'negative' => '買えない',
                    ],
                    'passive' => [
                        'affirmative' => '買われる',
                        'negative' => '買われない',
                    ],
                    'causative' => [
                        'affirmative' => '買わせる',
                        'negative' => '買わせない',
                        'passive' => [
                            'affirmative' => '買わせられる',
                            'negative' => '買わせられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => '買え',
                        'negative' => '買うな',
                    ],
                ],
                false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '     脱ぐ',
                    ],
                ], [
                    'dictionary' => '脱ぐ',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => '脱ぐ',
                            'negative' => '脱がない',
                        ],
                        'polite' => [
                            'affirmative' => '脱ぎます',
                            'negative' => '脱ぎません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => '脱いだ', // da-ending
                            'negative' => '脱がなかった',
                        ],
                        'polite' => [
                            'affirmative' => '脱ぎました',
                            'negative' => '脱ぎませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => '脱いで', // de-ending
                        'negative' => '脱がなくて',
                    ],
                    'potential' => [
                        'affirmative' => '脱げる',
                        'negative' => '脱げない',
                    ],
                    'passive' => [
                        'affirmative' => '脱がれる',
                        'negative' => '脱がれない',
                    ],
                    'causative' => [
                        'affirmative' => '脱がせる',
                        'negative' => '脱がせない',
                        'passive' => [
                            'affirmative' => '脱がせられる',
                            'negative' => '脱がせられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => '脱げ',
                        'negative' => '脱ぐな',
                    ],
                ],
                false
            ],
            [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '   しゃべる  ',
                        'past' => [
                            'polite' => [
                                'affirmative' => '   つ   '
                            ],
                        ],
                        'imperative' => [
                            'negative' => 'わ'
                        ]
                    ],
                ], [
                    'dictionary' => 'しゃべる',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => 'しゃべる',
                            'negative' => 'しゃべらない',
                        ],
                        'polite' => [
                            'affirmative' => 'しゃべります',
                            'negative' => 'しゃべりません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => 'しゃべった',
                            'negative' => 'しゃべらなかった',
                        ],
                        'polite' => [
                            'affirmative' => 'つ',
                            'negative' => 'しゃべりませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => 'しゃべって',
                        'negative' => 'しゃべらなくて',
                    ],
                    'potential' => [
                        'affirmative' => 'しゃべれる',
                        'negative' => 'しゃべれない',
                    ],
                    'passive' => [
                        'affirmative' => 'しゃべられる',
                        'negative' => 'しゃべられない',
                    ],
                    'causative' => [
                        'affirmative' => 'しゃべらせる',
                        'negative' => 'しゃべらせない',
                        'passive' => [
                            'affirmative' => 'しゃべらせられる',
                            'negative' => 'しゃべらせられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => 'しゃべれ',
                        'negative' => 'わ',
                    ],
                ],
                false
            ],
            [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => '  する  ',
                    ],
                ], [
                    'dictionary' => 'する',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => 'する',
                            'negative' => 'しない',
                        ],
                        'polite' => [
                            'affirmative' => 'します',
                            'negative' => 'しません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => 'した', 
                            'negative' => 'しなかった',
                        ],
                        'polite' => [
                            'affirmative' => 'しました',
                            'negative' => 'しませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => 'して',
                        'negative' => 'しなくて',
                    ],
                    'potential' => [
                        'affirmative' => 'できる',
                        'negative' => 'できない',
                    ],
                    'passive' => [
                        'affirmative' => 'される',
                        'negative' => 'されない',
                    ],
                    'causative' => [
                        'affirmative' => 'させる',
                        'negative' => 'させない',
                        'passive' => [
                            'affirmative' => 'させられる',
                            'negative' => 'させられない',
                        ]
                    ],
                    'imperative' => [
                        'affirmative' => 'しろ',
                        'negative' => 'するな',
                    ],
                ],
                true
            ],
            [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => '   為る ', /* する　alternative rarely 
                        used, should not be filled by autoconjugation */
                    ],
                ], [
                    'dictionary' => '為る',
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider conjugateValidProvider
     *
     * @param array $params
     * @param array $expected
     */
    public function testConjugateValid(
        array $params,
        array $expected,
        bool $isReviewed,
    ): void {
        $verb = new Verb();
        $verb
            ->setGroup($params['group'])
            ->setInflections($params['inflections'])
            ->conjugate();

        $this->assertEquals($verb->getInflections(), $expected);
        $this->assertEquals($verb->isReviewed(), $isReviewed);
    }

    /**
     * @return array<array<<string|bool>>
     */
    public function conjugateInvalidProvider(): array
    {
        // Get only invalid inflections
        $tests = array_filter(
            $this->isValidInflectionsProvider(),
            fn($test) => !$test[1] && is_array($test[0]['inflections'])
        );

        return array_column($tests, 0);
    }

    /**
     * @dataProvider conjugateInvalidProvider
     *
     * @param string $group
     * @param array $inflections
     */
    public function testConjugateInvalid(
        string $group,
        array $inflections,
    ): void 
    {    
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Set inflections are not valid');

        $verb = new Verb();
        $verb
            ->setGroup($group)
            ->setInflections($inflections)
            ->conjugate();
    }
}
