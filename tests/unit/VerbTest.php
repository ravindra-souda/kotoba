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
     * @return array<array<array<mixed>|int>>>
     */
    public function hasValidGroupProvider(): array
    {
        return [
            '0_ichidan' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '食べる',
                    ],
                ], 0,
            ],
            '0_godan' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '飲む',
                    ],
                ], 0,
            ],
            '0_irregular' => [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => 'する',
                    ],
                ], 0,
            ],
            '1_ichidan_romaji_not_ending_with_る' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'taberu',
                    ],
                ], 1,
            ],
            '1_ichidan_kanji_not_ending_with_る' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '食う',
                    ],
                ], 1,
            ],
            '2_godan_kanji_with_incorrect_ending' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '食べ物',
                    ],
                ], 2,
            ],
            '2_godan_romaji_with_incorrect_ending' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'nomu',
                    ],
                ], 2,
            ],
            '3_not_an_irregular_verb' => [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => '知る',
                    ],
                ], 3,
            ],
            '4_する_not_ichidan' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'する',
                    ],
                ], 4,
            ],
            '4_する_not_godan' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'する',
                    ],
                ], 4,
            ],
            '4_来る_not_ichidan' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '来る',
                    ],
                ], 4,
            ],
            '4_来る_not_godan' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '来る',
                    ],
                ], 4,
            ],
            '4_くる_not_ichidan' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => 'くる',
                    ],
                ], 4,
            ],
            '4_くる_not_godan' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => 'くる',
                    ],
                ], 4,
            ],
            '5_inflections_null' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => null,
                ], 5,
            ],
            '5_inflections_empty' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => '',
                ], 5,
            ],
            '5_inflections_without_dictionary' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dict' => '食べる',
                    ],
                ], 5,
            ],
            '5_inflections_dictionary_empty' => [
                [
                    'group' => Verb::ICHIDAN,
                    'inflections' => [
                        'dictionary' => '',
                    ],
                ], 5,
            ],
        ];
    }

    /**
     * @dataProvider hasValidGroupProvider
     *
     * @param array<mixed> $params
     */
    public function testHasValidGroup(
        array $params,
        int $expected,
    ): void {
        $verb = new Verb();
        $verb->setGroup($params['group']);

        $this->assertEquals(
            $verb->hasValidGroup(
                $params['inflections']
            ),
            $expected
        );
    }

    /**
     * @return array<array<array<mixed>|bool>>>
     */
    public function conjugateValidProvider(): array
    {
        return [
            'ichidan' => [
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => '見ろ',
                        'negative' => '見るな',
                    ],
                ],
                false,
            ],
            'godan' => [
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => '聞け',
                        'negative' => '聞くな',
                    ],
                ],
                false,
            ],
            'godan_exception_u' => [
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => '買え',
                        'negative' => '買うな',
                    ],
                ],
                false,
            ],
            'godan_exception_da' => [
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => '脱げ',
                        'negative' => '脱ぐな',
                    ],
                ],
                false,
            ],
            'inflections_partially_filled' => [
                [
                    'group' => Verb::GODAN,
                    'inflections' => [
                        'dictionary' => '   しゃべる  ',
                        'past' => [
                            'polite' => [
                                'affirmative' => '   つ   ',
                            ],
                        ],
                        'imperative' => [
                            'negative' => 'わ',
                        ],
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => 'しゃべれ',
                        'negative' => 'わ',
                    ],
                ],
                false,
            ],
            'する' => [
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
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => 'しろ',
                        'negative' => 'するな',
                    ],
                ],
                true,
            ],
            '為る' => [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => '   為る ',
                    ],
                ], [
                    'dictionary' => '為る',
                    'non-past' => [
                        'informal' => [
                            'affirmative' => '為る',
                            'negative' => '為ない',
                        ],
                        'polite' => [
                            'affirmative' => '為ます',
                            'negative' => '為ません',
                        ],
                    ],
                    'past' => [
                        'informal' => [
                            'affirmative' => '為た',
                            'negative' => '為なかった',
                        ],
                        'polite' => [
                            'affirmative' => '為ました',
                            'negative' => '為ませんでした',
                        ],
                    ],
                    'te' => [
                        'affirmative' => '為て',
                        'negative' => '為なくて',
                    ],
                    'potential' => [
                        'affirmative' => 'できる',
                        'negative' => 'できない',
                    ],
                    'passive' => [
                        'affirmative' => '為れる',
                        'negative' => '為れない',
                    ],
                    'causative' => [
                        'affirmative' => '為せる',
                        'negative' => '為せない',
                        'passive' => [
                            'affirmative' => '為せられる',
                            'negative' => '為せられない',
                        ],
                    ],
                    'imperative' => [
                        'affirmative' => '為ろ',
                        'negative' => '為るな',
                    ],
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider conjugateValidProvider
     *
     * @param array<mixed> $params
     * @param array<mixed> $expected
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
            ->conjugate()
        ;

        $this->assertEquals($verb->getInflections(), $expected);
        $this->assertEquals($verb->isReviewed(), $isReviewed);
    }

    /**
     * @return array<mixed>
     */
    public function conjugateInvalidProvider(): array
    {
        $provider = [];

        // Get only invalid inflections
        foreach ($this->hasValidGroupProvider() as $key => $test) {
            if (0 !== $test[1] && is_array($test[0]['inflections'])) {
                $provider[$key] = $test[0];
            }
        }

        return $provider;
    }

    /**
     * @dataProvider conjugateInvalidProvider
     *
     * @param array<mixed> $inflections
     */
    public function testConjugateInvalid(
        string $group,
        array $inflections,
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Set inflections are not valid or group mismatch'
        );

        $verb = new Verb();
        $verb
            ->setGroup($group)
            ->setInflections($inflections)
            ->conjugate()
        ;
    }
}
