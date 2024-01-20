<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Verb;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class VerbsPostTest extends ApiTestCase
{   
    private const POST_COMPLETE_VALID_VERBS = [
        'godan' => [
            'romaji' => '   iKu  ',
            'hiragana' => '  いく   ',
            'kanji' => ' 行く   ',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => '  to GO   ',
            ],
            'inflections' => [
                'dictionary' => '  行く  ',
            ],
        ],
        'ichidan' => [
            'romaji' => '  tabEru  ',
            'hiragana' => '      たべる',
            'kanji' => '食べる     ',
            'jlpt' => 5,
            'group' => 'ichidan',
            'meaning' => [
                'en' => '   TO eat',
            ],
            'inflections' => [
                'dictionary' => '   食べる  ',
            ],
        ],
        'irregular' => [
            'romaji' => '  kuRu ',
            'hiragana' => '   くる ',
            'kanji' => ' 来る   ',
            'jlpt' => 5,
            'group' => 'irregular',
            'meaning' => [
                'en' => '    to COME  ',
            ],
            'inflections' => [
                'dictionary' => '    来る   ',
            ],
        ],
        'partial_inflections' => [
            /* only left out inflections should be filled by auto-conjugation */
            'romaji' => '  maNaBu ',
            'hiragana' => '   まなぶ  ',
            'kanji' => ' 学ぶ   ',
            'jlpt' => 3,
            'group' => 'godan',
            'meaning' => [
                'en' => '    to LEarn  ',
            ],
            'inflections' => [
                'dictionary' => '  学ぶ   ',
                'past' => [
                    'informal' => [
                        'affirmative' => '   あ     ', 
                    ],
                ],
                'te' => [
                    'negative' => '   て ',
                ],
                'causative' => [
                    'passive' => [
                        'negative' => 'く    ',
                    ]
                ],
            ],
        ],
    ];

    private const POST_COMPLETE_EXPECTED_VERBS = [
        'godan' => [
            ...self::POST_COMPLETE_VALID_VERBS['godan'],
            'romaji' => 'iku',
            'hiragana' => 'いく',
            'kanji' => '行く',
            'meaning' => [
                'en' => 'to go',
            ],
            'inflections' => [
                'dictionary' => '行く',
                'non-past' => [
                    'informal' => [
                        'affirmative' => '行く',
                        'negative' => '行かない',
                    ],
                    'polite' => [
                        'affirmative' => '行きます',
                        'negative' => '行きません',
                    ],
                ],
                'past' => [
                    'informal' => [
                        'affirmative' => '行かた', /* automatic conjugation 
                        should be wrong since this an exception for this verb
                        in the japanese langage, user can correct this 
                        afterwards */
                        'negative' => '行かなかった',
                    ],
                    'polite' => [
                        'affirmative' => '行きました',
                        'negative' => '行きませんでした',
                    ],
                ],
                'te' => [
                    'affirmative' => '行って',
                    'negative' => '行かなくて',
                ],
                'potential' => [
                    'affirmative' => '行ける',
                    'negative' => '行けない',
                ],
                'passive' => [
                    'affirmative' => '行かれる',
                    'negative' => '行かれない',
                ],
                'causative' => [
                    'affirmative' => '行かせる',
                    'negative' => '行かせない',
                    'passive' => [
                        'affirmative' => '行かせられる',
                        'negative' => '行かせられない',
                    ]
                ],
                'imperative' => [
                    'affirmative' => '行け',
                    'negative' => '行くな',
                ],
            ],
        ],
        'ichidan' => [
            ...self::POST_COMPLETE_VALID_VERBS['ichidan'],
            'romaji' => 'taberu',
            'hiragana' => 'たべる',
            'kanji' => '食べる',
            'meaning' => [
                'en' => 'to eat',
            ],
            'inflections' => [
                'dictionary' => '食べる',
                'non-past' => [
                    'informal' => [
                        'affirmative' => '食べる',
                        'negative' => '食べない',
                    ],
                    'polite' => [
                        'affirmative' => '食べます',
                        'negative' => '食べません',
                    ],
                ],
                'past' => [
                    'informal' => [
                        'affirmative' => '食べた', 
                        'negative' => '食べなかった',
                    ],
                    'polite' => [
                        'affirmative' => '食べました',
                        'negative' => '食べませんでした',
                    ],
                ],
                'te' => [
                    'affirmative' => '食べて',
                    'negative' => '食べなくて',
                ],
                'potential' => [
                    'affirmative' => '食べられる',
                    'negative' => '食べられない',
                ],
                'passive' => [
                    'affirmative' => '食べられる',
                    'negative' => '食べられない',
                ],
                'causative' => [
                    'affirmative' => '食べさせる',
                    'negative' => '食べさせない',
                    'passive' => [
                        'affirmative' => '食べさせられる',
                        'negative' => '食べさせられない',
                    ]
                ],
                'imperative' => [
                    'affirmative' => '食べろ',
                    'negative' => '食べるな',
                ],
            ],
        ],
        'irregular' => [
            ...self::POST_COMPLETE_VALID_VERBS['ichidan'],
            /* automatic conjugation must be disabled for irregular verbs,
            leaving the completion to the user */
            'romaji' => 'kuru',
            'hiragana' => 'くる',
            'kanji' => '来る',
            'meaning' => [
                'en' => 'to come',
            ],
            'inflections' => [
                'dictionary' => '来る',
            ],
        ],
        'partial_inflections' => [
            ...self::POST_COMPLETE_VALID_VERBS['partial_inflections'],
            'romaji' => 'manabu',
            'hiragana' => 'まなぶ',
            'kanji' => '学ぶ',
            'meaning' => [
                'en' => 'to learn',
            ],
            'inflections' => [
                'dictionary' => '学ぶ',
                'non-past' => [
                    'informal' => [
                        'affirmative' => '学ぶ',
                        'negative' => '学ばない',
                    ],
                    'polite' => [
                        'affirmative' => '学びます',
                        'negative' => '学びません',
                    ],
                ],
                'past' => [
                    'informal' => [
                        'affirmative' => 'あ', 
                        'negative' => '学ばなかった',
                    ],
                    'polite' => [
                        'affirmative' => '学びました',
                        'negative' => '学びませんでした',
                    ],
                ],
                'te' => [
                    'affirmative' => '学んで',
                    'negative' => 'て',
                ],
                'potential' => [
                    'affirmative' => '学べる',
                    'negative' => '学べない',
                ],
                'passive' => [
                    'affirmative' => '学ばれる',
                    'negative' => '学ばれない',
                ],
                'causative' => [
                    'affirmative' => '学ばせる',
                    'negative' => '学ばせない',
                    'passive' => [
                        'affirmative' => '学ばせられる',
                        'negative' => 'く',
                    ]
                ],
                'imperative' => [
                    'affirmative' => '学べ',
                    'negative' => '学ぶな',
                ],
            ],
        ],
    ];

    private const POST_MINIMAL_VALID_VERB = [
        'romaji' => 'taberu',
        'hiragana' => 'たべる',
        'group' => 'ichidan',
        'meaning' => [
            'en' => 'to eat',
        ]
    ];

    private const POST_INVALID_VERBS = [
        'romaji_empty' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'romaji' => '',
        ],
        'romaji_maxlength' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'romaji' => '*',
        ],
        'romaji_written_in_kana' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'romaji' => 'ローマジ',
        ],
        'no_hiragana_nor_katakana' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'hiragana' => '',
            'katakana' => '',
        ],
        'hiragana_written_in_katakana' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'hiragana' => 'カタカナ',
        ],
        'hiragana_maxlength' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'hiragana' => '*',
        ],
        'katakana_written_in_hiragana' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'katakana' => 'ひらがな',
        ],
        'katakana_maxlength' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'katakana' => '*',
        ],
        'kanji_maxlength' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'kanji' => '*',
        ],
        'kanji_written_in_romaji' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'kanji' => 'kanji',
        ],
        'meaning_empty' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'meaning' => '',
        ],
        'meaning_not_an_array' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'meaning' => 'to eat',
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'meaning' => [
                'en' => 'to eat',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'group_verb' => [
            ...self::POST_COMPLETE_VALID_VERBS['godan'],
            'group' => 'i',
        ],
        'jlpt_not_an_integer' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'jlpt' => 1.1,
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_VERB,
            'jlpt' => 6,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validVerbsProvider(): array
    {
        $provider = [];

        foreach(self::POST_COMPLETE_VALID_VERBS as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_VERBS[$key] ?? $value;
            $provider[] = [$value, $expected];
        }
        return $provider;
    }

    /**
     * @dataProvider validVerbsProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testVerbsPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Verb::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('createdAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['createdAt']);
        $this->assertMatchesRegularExpression(
            '/\d+-'.$expected['romaji'].'/',
            $content['code']
        );
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidVerbsProvider(): array
    {
        return [
            [
                self::POST_INVALID_VERBS['romaji_empty'],
                'romaji: '.Verb::VALIDATION_ERR_EMPTY,
            ],
            [
                [
                    ...self::POST_INVALID_VERBS['romaji_maxlength'],
                    'romaji' => str_repeat('a', Verb::ROMAJI_MAXLENGTH + 1),
                ],
                'romaji: '.Verb::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_VERBS['romaji_written_in_kana'],
                'romaji: '.Verb::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_VERBS['no_hiragana_nor_katakana'],
                'hiragana, katakana: '.
                Verb::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_VERBS['hiragana_written_in_katakana'],
                'hiragana: '.Verb::VALIDATION_ERR_HIRAGANA,
            ],
            [
                [
                    ...self::POST_INVALID_VERBS['hiragana_maxlength'],
                    'hiragana' => 
                        str_repeat('あ', Verb::HIRAGANA_MAXLENGTH + 1),
                ],
                'hiragana: '.Verb::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_VERBS['katakana_written_in_hiragana'],
                'katakana: '.Verb::VALIDATION_ERR_KATAKANA,
            ],
            [
                [
                    ...self::POST_INVALID_VERBS['katakana_maxlength'],
                    'katakana' => 
                        str_repeat('ア', Verb::KATAKANA_MAXLENGTH + 1),
                ],
                'katakana: '.Verb::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                [
                    ...self::POST_INVALID_VERBS['kanji_maxlength'],
                    'kanji' => str_repeat('字', Verb::KANJI_MAXLENGTH + 1),
                ],
                'kanji: '.Verb::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_VERBS['kanji_written_in_romaji'],
                'kanji: '.Verb::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_VERBS['meaning_empty'],
                'meaning: '.Verb::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_VERBS['meaning_not_an_array'],
                'meaning: '.Verb::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_VERBS['meaning_lang_unknown'],
                'meaning: '.Verb::VALIDATION_ERR_MEANING,
            ],
            [
                self::POST_INVALID_VERBS['group_verb'],
                'group: '.Verb::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_VERBS['jlpt_not_an_integer'],
                'jlpt: '.Verb::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_VERBS['jlpt_min'],
                'jlpt: '.Verb::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_VERBS['jlpt_max'],
                'jlpt: '.Verb::VALIDATION_ERR_JLPT,
            ],
        ];
    }

    /**
     * @dataProvider invalidVerbsProvider
     *
     * @param array<string> $payload
     */
    public function testVerbsPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }
}
