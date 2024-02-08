<?php

declare(strict_types=1);

namespace App\Document\Trait\Const;

trait VerbTrait
{
    private const ICHIDAN_INFLECTIONS = [
        'non-past' => [
            'informal' => [
                'affirmative' => '',
                'negative' => 'ない',
            ],
            'polite' => [
                'affirmative' => 'ます',
                'negative' => 'ません',
            ],
        ],
        'past' => [
            'informal' => [
                'affirmative' => 'た',
                'negative' => 'なかった',
            ],
            'polite' => [
                'affirmative' => 'ました',
                'negative' => 'ませんでした',
            ],
        ],
        'te' => [
            'affirmative' => 'て',
            'negative' => 'なくて',
        ],
        'potential' => [
            'affirmative' => 'られる',
            'negative' => 'られない',
        ],
        'passive' => [
            'affirmative' => 'られる',
            'negative' => 'られない',
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
            'affirmative' => 'ろ',
            'negative' => 'るな',
        ],
    ];

    private const GODAN_INFLECTIONS = [
        'non-past' => [
            'informal' => [
                'affirmative' => '',
                'negative' => '{a}ない',
            ],
            'polite' => [
                'affirmative' => '{i}ます',
                'negative' => '{i}ません',
            ],
        ],
        'past' => [
            'informal' => [
                'affirmative' => '{i-past}',
                'negative' => '{a}なかった',
            ],
            'polite' => [
                'affirmative' => '{i}ました',
                'negative' => '{i}ませんでした',
            ],
        ],
        'te' => [
            'affirmative' => '{i-te}',
            'negative' => '{a}なくて',
        ],
        'potential' => [
            'affirmative' => '{e}る',
            'negative' => '{e}ない',
        ],
        'passive' => [
            'affirmative' => '{a}れる',
            'negative' => '{a}れない',
        ],
        'causative' => [
            'affirmative' => '{a}せる',
            'negative' => '{a}せない',
            'passive' => [
                'affirmative' => '{a}せられる',
                'negative' => '{a}せられない',
            ],
        ],
        'imperative' => [
            'affirmative' => '{e}',
            'negative' => '{u}な',
        ],
    ];

    private const OKURIGANA = [
        'う' => ['わ', 'い', 'う', 'え', 'お', 'った', 'って'],
        'く' => ['か', 'き', 'く', 'け', 'こ', 'いた', 'いて'],
        'ぐ' => ['が', 'ぎ', 'ぐ', 'げ', 'ご', 'いだ', 'いで'],
        'す' => ['さ', 'し', 'す', 'せ', 'そ', 'した', 'して'],
        'つ' => ['た', 'ち', 'つ', 'て', 'と', 'った', 'って'],
        'ぬ' => ['な', 'に', 'ぬ', 'ね', 'の', 'んだ', 'んで'],
        'ぶ' => ['ば', 'び', 'ぶ', 'べ', 'ぼ', 'んだ', 'んで'],
        'む' => ['ま', 'み', 'む', 'め', 'も', 'んだ', 'んで'],
        'る' => ['ら', 'り', 'る', 'れ', 'ろ', 'った', 'って'],
    ];

    private const IRREGULAR_INFLECTIONS = [
        'する' => [
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
        '為る' => [
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
        'くる' => [
            'dictionary' => 'くる',
            'non-past' => [
                'informal' => [
                    'affirmative' => 'くる',
                    'negative' => 'こない',
                ],
                'polite' => [
                    'affirmative' => 'きます',
                    'negative' => 'きません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => 'きた',
                    'negative' => 'こなかった',
                ],
                'polite' => [
                    'affirmative' => 'きました',
                    'negative' => 'きませんでした',
                ],
            ],
            'te' => [
                'affirmative' => 'きて',
                'negative' => 'こなくて',
            ],
            'potential' => [
                'affirmative' => 'こられる',
                'negative' => 'こられない',
            ],
            'passive' => [
                'affirmative' => 'こられる',
                'negative' => 'こられない',
            ],
            'causative' => [
                'affirmative' => 'こさせる',
                'negative' => 'こさせない',
                'passive' => [
                    'affirmative' => 'こさせられる',
                    'negative' => 'こさせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => 'こい',
                'negative' => 'くるな',
            ],
        ],
        '来る' => [
            'dictionary' => '来る',
            'non-past' => [
                'informal' => [
                    'affirmative' => '来る',
                    'negative' => '来ない',
                ],
                'polite' => [
                    'affirmative' => '来ます',
                    'negative' => '来ません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '来た',
                    'negative' => '来なかった',
                ],
                'polite' => [
                    'affirmative' => '来ました',
                    'negative' => '来ませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '来て',
                'negative' => '来なくて',
            ],
            'potential' => [
                'affirmative' => '来られる',
                'negative' => '来られない',
            ],
            'passive' => [
                'affirmative' => '来られる',
                'negative' => '来られない',
            ],
            'causative' => [
                'affirmative' => '来させる',
                'negative' => '来させない',
                'passive' => [
                    'affirmative' => '来させられる',
                    'negative' => '来させられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '来い',
                'negative' => '来るな',
            ],
        ],
    ];
}
