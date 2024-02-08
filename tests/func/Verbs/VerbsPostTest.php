<?php

declare(strict_types=1);

namespace App\Tests;

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
    use Trait\BuildProviderTrait;

    private const POST_COMPLETE_VALID_VERBS = [
        'godan' => [
            'hiragana' => '  いく   ',
            'kanji' => ' 行く   ',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => ['  to GO   '],
            ],
            'inflections' => [
                'dictionary' => '  行く  ',
            ],
        ],
        'ichidan' => [
            'hiragana' => '      たべる',
            'kanji' => '食べる     ',
            'jlpt' => 5,
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['   TO eat'],
            ],
            'inflections' => [
                'dictionary' => '   食べる  ',
            ],
        ],
        'irregular' => [
            'hiragana' => '   くる ',
            'kanji' => ' 来る   ',
            'jlpt' => 5,
            'group' => 'irregular',
            'meaning' => [
                'en' => ['    to COME  '],
            ],
            'inflections' => [
                'dictionary' => '    来る   ',
            ],
        ],
        'romaji_filled' => [
            'romaji' => '  coMe ',
            'hiragana' => '   くる ',
            'jlpt' => 5,
            'group' => 'irregular',
            'meaning' => [
                'en' => ['    to COME  '],
            ],
            'inflections' => [
                'dictionary' => '    くる   ',
            ],
        ],
        'partial_inflections' => [
            // only left out inflections should be filled by auto-conjugation
            'hiragana' => '   まなぶ  ',
            'kanji' => ' 学ぶ   ',
            'jlpt' => 3,
            'group' => 'godan',
            'meaning' => [
                'en' => ['    to LEarn  '],
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
                    ],
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
                'en' => ['to go'],
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
                        'affirmative' => '行った', /* even though there's an
                        exception for this verb, automatic conjugation should
                        be fine */
                        'negative' => '行かなかった',
                    ],
                    'polite' => [
                        'affirmative' => '行きました',
                        'negative' => '行きませんでした',
                    ],
                ],
                'te' => [
                    'affirmative' => '行って', // same exception here
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
                    ],
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
                'en' => ['to eat'],
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
                    ],
                ],
                'imperative' => [
                    'affirmative' => '食べろ',
                    'negative' => '食べるな',
                ],
            ],
        ],
        'irregular' => [
            ...self::POST_COMPLETE_VALID_VERBS['irregular'],
            'romaji' => 'kuru',
            'hiragana' => 'くる',
            'kanji' => '来る',
            'meaning' => [
                'en' => ['to come'],
            ],
            'inflections' => [
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
        ],
        'romaji_filled' => [
            ...self::POST_COMPLETE_VALID_VERBS['romaji_filled'],
            'romaji' => 'come',
            'hiragana' => 'くる',
            'meaning' => [
                'en' => ['to come'],
            ],
            'inflections' => [
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
        ],
        'partial_inflections' => [
            ...self::POST_COMPLETE_VALID_VERBS['partial_inflections'],
            'romaji' => 'manabu',
            'hiragana' => 'まなぶ',
            'kanji' => '学ぶ',
            'meaning' => [
                'en' => ['to learn'],
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
                    ],
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
            'en' => ['to eat'],
        ],
        'inflections' => [
            'dictionary' => 'たべる',
        ],
    ];

    private const POST_INVALID_VERBS = [
        'romaji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_VERB,
            'maxlength' => [
                'romaji' => 'x',
            ],
            'message' => [
                'text' => 'romaji: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::ROMAJI_MAXLENGTH,
            ],
        ],

        'romaji_written_in_kana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'romaji' => 'ローマジ',
            ],
            'message' => 'romaji: '.Verb::VALIDATION_ERR_ROMAJI,
        ],
        'no_hiragana_nor_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Verb::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Verb::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'hiragana_written_in_katakana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'hiragana' => 'カタカナ',
            ],
            'message' => 'hiragana: '.Verb::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_VERB,
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana_written_in_hiragana' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'katakana' => 'ひらがな',
            ],
            'message' => 'katakana: '.Verb::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_VERB,
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::KATAKANA_MAXLENGTH,
            ],
        ],
        'kanji_written_in_romaji' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'kanji' => 'kanji',
            ],
            'message' => 'kanji: '.Verb::VALIDATION_ERR_KANJI,
        ],
        'kanji_maxlength' => [
            'payload' => self::POST_MINIMAL_VALID_VERB,
            'maxlength' => [
                'kanji' => '字',
            ],
            'message' => [
                'text' => 'kanji: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::KANJI_MAXLENGTH,
            ],
        ],
        'meaning_mandatory_lang_missing' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'meaning' => [
                    'fr' => ['manger'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[1],
                'values' => Verb::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_lang_unknown' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'meaning' => [
                    'en' => ['to eat'],
                    'dummy' => ['🂡🂱🃁🃑'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[2],
                'values' => Verb::ALLOWED_LANGS,
            ],
        ],
        'meaning_empty' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'meaning' => [
                    'en' => ['  '],
                ],
            ],
            'message' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[3],
        ],
        'meaning_invalid' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'meaning' => [
                    'en' => 'to eat',
                ],
            ],
            'message' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[3],
        ],
        'group' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['godan'],
                'group' => 'i',
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_ENUM,
                'values' => Verb::ALLOWED_GROUPS,
            ],
        ],
        'group_invalid_ichidan' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['ichidan'],
                'inflections' => [
                    'dictionary' => '行く',
                ],
            ],
            'message' => 'group: '.Verb::VALIDATION_ERR_ICHIDAN,
        ],
        'group_invalid_godan' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['godan'],
                'inflections' => [
                    'dictionary' => 'iku',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_GODAN,
                'values' => Verb::VALID_GODAN_ENDINGS,
            ],
        ],
        'group_invalid_irregular' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['irregular'],
                'inflections' => [
                    'dictionary' => 'いる',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_IRREGULAR,
                'values' => Verb::IRREGULAR_VERBS,
            ],
        ],
        'group_invalid_is_irregular' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['ichidan'],
                'inflections' => [
                    'dictionary' => 'する',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_IS_IRREGULAR,
                'values' => Verb::IRREGULAR_VERBS,
            ],
        ],
        'inflections' => [
            'payload' => [
                ...self::POST_COMPLETE_VALID_VERBS['ichidan'],
                'inflections' => [],
            ],
            'message' => 'inflections: '.Verb::VALIDATION_ERR_DICTIONARY,
        ],
        'inflections_empty_dictionary' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'inflections' => [
                    'dictionary' => '             ',
                ],
            ],
            'message' => 'inflections: '.Verb::VALIDATION_ERR_DICTIONARY,
        ],
        'jlpt_min' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'jlpt' => 0,
            ],
            'message' => 'jlpt: '.Verb::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'payload' => [
                ...self::POST_MINIMAL_VALID_VERB,
                'jlpt' => 6,
            ],
            'message' => 'jlpt: '.Verb::VALIDATION_ERR_JLPT,
        ],
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validVerbsProvider(): array
    {
        $provider = [];

        foreach (self::POST_COMPLETE_VALID_VERBS as $key => $value) {
            $expected = self::POST_COMPLETE_EXPECTED_VERBS[$key] ?? $value;
            $provider[$key] = [$value, $expected];
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
     * @return array<array<array<mixed>>>
     */
    public function invalidVerbsProvider(): array
    {
        return $this->buildPostProvider(self::POST_INVALID_VERBS);
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
