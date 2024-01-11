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
                ]
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
                            'negative' => '買わない',
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
                ]
            ],
            [
                [
                    'group' => Verb::IRREGULAR,
                    'inflections' => [
                        'dictionary' => '  する  ',
                    ],
                ], [
                    'dictionary' => 'する',
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
    public function testConjugateValid(
        array $params,
        array $expected,
    ): void {
        $verb = new Verb();
        $verb
            ->setGroup($params['group'])
            ->setInflections($params['inflections'])
            ->conjugate();

        $this->assertEquals($verb->getInflections(), $expected);
        $this->assertEquals($verb->isReviewed(), false);
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
