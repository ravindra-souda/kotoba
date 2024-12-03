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
class VerbsPutTest extends ApiTestCase
{
    use Trait\BuildProviderTrait;

    private const PUT_FIXTURE_VERBS = [
        'godan' => [
            'hiragana' => 'わかる',
            'kanji' => '分かる',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => ['to understand'],
            ],
            'inflections' => [
                'dictionary' => '分かる',
            ],
        ],
        'ichidan' => [
            'hiragana' => 'おきる',
            'kanji' => '起きる',
            'jlpt' => 5,
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['to wake up'],
            ],
            'inflections' => [
                'dictionary' => '起きる',
            ],
        ],
        'false_godan' => [
            'hiragana' => 'あける',
            'kanji' => '開ける',
            'group' => 'godan',
            'meaning' => [
                'en' => ['to open'],
            ],
            'inflections' => [
                'dictionary' => '開ける',
            ],
        ],
        'false_ichidan' => [
            'hiragana' => 'はいる',
            'kanji' => '入る',
            'group' => 'ichidan',
            'meaning' => [
                'en' => ['to enter'],
            ],
            'inflections' => [
                'dictionary' => '入る',
            ],
        ],
        'irregular' => [
            'hiragana' => 'する',
            'jlpt' => 5,
            'group' => 'irregular',
            'meaning' => [
                'en' => ['to do'],
            ],
            'inflections' => [
                'dictionary' => 'する',
            ],
        ],
    ];

    private const PUT_VALID_VERBS = [
        'hiragana' => [
            'fixture' => 'godan',
            'payload' => [
                'hiragana' => 'つくる   ',
            ],
        ],
        'katakana' => [
            'fixture' => 'ichidan',
            'payload' => [
                'hiragana' => '   ',
                'katakana' => ' タベル  ',
            ],
        ],
        'katakana_2' => [
            'fixture' => 'godan',
            'payload' => [
                'hiragana' => '   ',
                'katakana' => 'デモる  ',
                'meaning' => [
                    'en' => ['  to DEMOnstrate (e.g. in the streets)  '],
                ],
                'inflections' => [
                    'dictionary' => ' デモる   ',
                ],
            ],
        ],
        'kanji' => [
            'fixture' => 'godan',
            'payload' => [
                'kanji' => ' 作る   ',
            ],
        ],
        'group_ichidan' => [
            'fixture' => 'false_godan',
            'payload' => [
                'group' => 'ichidan',
            ],
        ],
        'group_godan' => [
            'fixture' => 'false_ichidan',
            'payload' => [
                'group' => 'godan',
            ],
        ],
        'meaning' => [
            'fixture' => 'ichidan',
            'payload' => [
                'meaning' => [
                    'en' => ['   TO EAT'],
                    'fr' => ['MANGER    '],
                ],
            ],
        ],
        'romaji' => [
            'fixture' => 'godan',
            'payload' => [
                'romaji' => '    tsukuru   ',
            ],
        ],
        'dictionary' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [
                    'dictionary' => '    作る   ',
                ],
            ],
        ],
        'inflections' => [
            'fixture' => 'ichidan',
            'payload' => [
                'inflections' => [
                    'dictionary' => '起きる   ',
                    'causative' => [
                        'passive' => [
                            'negative' => '   あ',
                        ],
                    ],
                ],
            ],
        ],
        'dictionary_and_inflections' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [
                    'dictionary' => ' 飲む     ',
                    'past' => [
                        'informal' => [
                            'affirmative' => 'い   ',
                        ],
                    ],
                    'te' => [
                        'negative' => '    て',
                    ],
                ],
            ],
        ],
        'jlpt' => [
            'fixture' => 'ichidan',
            'payload' => [
                'jlpt' => 4,
            ],
        ],
        'irregular' => [
            'fixture' => 'irregular',
            'payload' => [
                'hiragana' => '    くる  ',
                'kanji' => '来る  ',
                'inflections' => [
                    'dictionary' => '    くる   ',
                ],
            ],
        ],
    ];

    private const EXPECTED_INFLECTIONS = [
        'godan' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '分かる',
                    'negative' => '分からない',
                ],
                'polite' => [
                    'affirmative' => '分かります',
                    'negative' => '分かりません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '分かった',
                    'negative' => '分からなかった',
                ],
                'polite' => [
                    'affirmative' => '分かりました',
                    'negative' => '分かりませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '分かって',
                'negative' => '分からなくて',
            ],
            'potential' => [
                'affirmative' => '分かれる',
                'negative' => '分かれない',
            ],
            'passive' => [
                'affirmative' => '分かられる',
                'negative' => '分かられない',
            ],
            'causative' => [
                'affirmative' => '分からせる',
                'negative' => '分からせない',
                'passive' => [
                    'affirmative' => '分からせられる',
                    'negative' => '分からせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '分かれ',
                'negative' => '分かるな',
            ],
        ],
        'ichidan' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '起きる',
                    'negative' => '起きない',
                ],
                'polite' => [
                    'affirmative' => '起きます',
                    'negative' => '起きません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '起きた',
                    'negative' => '起きなかった',
                ],
                'polite' => [
                    'affirmative' => '起きました',
                    'negative' => '起きませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '起きて',
                'negative' => '起きなくて',
            ],
            'potential' => [
                'affirmative' => '起きられる',
                'negative' => '起きられない',
            ],
            'passive' => [
                'affirmative' => '起きられる',
                'negative' => '起きられない',
            ],
            'causative' => [
                'affirmative' => '起きさせる',
                'negative' => '起きさせない',
                'passive' => [
                    'affirmative' => '起きさせられる',
                    'negative' => '起きさせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '起きろ',
                'negative' => '起きるな',
            ],
        ],
        'irregular' => [
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
        'katakana_2' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => 'デモる',
                    'negative' => 'デモらない',
                ],
                'polite' => [
                    'affirmative' => 'デモります',
                    'negative' => 'デモりません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => 'デモった',
                    'negative' => 'デモらなかった',
                ],
                'polite' => [
                    'affirmative' => 'デモりました',
                    'negative' => 'デモりませんでした',
                ],
            ],
            'te' => [
                'affirmative' => 'デモって',
                'negative' => 'デモらなくて',
            ],
            'potential' => [
                'affirmative' => 'デモれる',
                'negative' => 'デモれない',
            ],
            'passive' => [
                'affirmative' => 'デモられる',
                'negative' => 'デモられない',
            ],
            'causative' => [
                'affirmative' => 'デモらせる',
                'negative' => 'デモらせない',
                'passive' => [
                    'affirmative' => 'デモらせられる',
                    'negative' => 'デモらせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => 'デモれ',
                'negative' => 'デモるな',
            ],
        ],
        'group_ichidan' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '開ける',
                    'negative' => '開けない',
                ],
                'polite' => [
                    'affirmative' => '開けます',
                    'negative' => '開けません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '開けた',
                    'negative' => '開けなかった',
                ],
                'polite' => [
                    'affirmative' => '開けました',
                    'negative' => '開けませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '開けて',
                'negative' => '開けなくて',
            ],
            'potential' => [
                'affirmative' => '開けられる',
                'negative' => '開けられない',
            ],
            'passive' => [
                'affirmative' => '開けられる',
                'negative' => '開けられない',
            ],
            'causative' => [
                'affirmative' => '開けさせる',
                'negative' => '開けさせない',
                'passive' => [
                    'affirmative' => '開けさせられる',
                    'negative' => '開けさせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '開けろ',
                'negative' => '開けるな',
            ],
        ],
        'group_godan' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '入る',
                    'negative' => '入らない',
                ],
                'polite' => [
                    'affirmative' => '入ります',
                    'negative' => '入りません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '入った',
                    'negative' => '入らなかった',
                ],
                'polite' => [
                    'affirmative' => '入りました',
                    'negative' => '入りませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '入って',
                'negative' => '入らなくて',
            ],
            'potential' => [
                'affirmative' => '入れる',
                'negative' => '入れない',
            ],
            'passive' => [
                'affirmative' => '入られる',
                'negative' => '入られない',
            ],
            'causative' => [
                'affirmative' => '入らせる',
                'negative' => '入らせない',
                'passive' => [
                    'affirmative' => '入らせられる',
                    'negative' => '入らせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '入れ',
                'negative' => '入るな',
            ],
        ],
        'dictionary' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '作る',
                    'negative' => '作らない',
                ],
                'polite' => [
                    'affirmative' => '作ります',
                    'negative' => '作りません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '作った',
                    'negative' => '作らなかった',
                ],
                'polite' => [
                    'affirmative' => '作りました',
                    'negative' => '作りませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '作って',
                'negative' => '作らなくて',
            ],
            'potential' => [
                'affirmative' => '作れる',
                'negative' => '作れない',
            ],
            'passive' => [
                'affirmative' => '作られる',
                'negative' => '作られない',
            ],
            'causative' => [
                'affirmative' => '作らせる',
                'negative' => '作らせない',
                'passive' => [
                    'affirmative' => '作らせられる',
                    'negative' => '作らせられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '作れ',
                'negative' => '作るな',
            ],
        ],
        'inflections' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '起きる',
                    'negative' => '起きない',
                ],
                'polite' => [
                    'affirmative' => '起きます',
                    'negative' => '起きません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => '起きた',
                    'negative' => '起きなかった',
                ],
                'polite' => [
                    'affirmative' => '起きました',
                    'negative' => '起きませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '起きて',
                'negative' => '起きなくて',
            ],
            'potential' => [
                'affirmative' => '起きられる',
                'negative' => '起きられない',
            ],
            'passive' => [
                'affirmative' => '起きられる',
                'negative' => '起きられない',
            ],
            'causative' => [
                'affirmative' => '起きさせる',
                'negative' => '起きさせない',
                'passive' => [
                    'affirmative' => '起きさせられる',
                    'negative' => 'あ',
                ],
            ],
            'imperative' => [
                'affirmative' => '起きろ',
                'negative' => '起きるな',
            ],
        ],
        'dictionary_and_inflections' => [
            'non-past' => [
                'informal' => [
                    'affirmative' => '飲む',
                    'negative' => '飲まない',
                ],
                'polite' => [
                    'affirmative' => '飲みます',
                    'negative' => '飲みません',
                ],
            ],
            'past' => [
                'informal' => [
                    'affirmative' => 'い',
                    'negative' => '飲まなかった',
                ],
                'polite' => [
                    'affirmative' => '飲みました',
                    'negative' => '飲みませんでした',
                ],
            ],
            'te' => [
                'affirmative' => '飲んで',
                'negative' => 'て',
            ],
            'potential' => [
                'affirmative' => '飲める',
                'negative' => '飲めない',
            ],
            'passive' => [
                'affirmative' => '飲まれる',
                'negative' => '飲まれない',
            ],
            'causative' => [
                'affirmative' => '飲ませる',
                'negative' => '飲ませない',
                'passive' => [
                    'affirmative' => '飲ませられる',
                    'negative' => '飲ませられない',
                ],
            ],
            'imperative' => [
                'affirmative' => '飲め',
                'negative' => '飲むな',
            ],
        ],
    ];

    private const PUT_EXPECTED_VERBS = [
        'hiragana' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'hiragana' => 'つくる',
                'romaji' => 'tsukuru',
                'inflections' => self::EXPECTED_INFLECTIONS['godan'],
            ],
            'code' => 'tsukuru',
        ],
        'katakana' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['ichidan'],
                'katakana' => 'タベル',
                'hiragana' => null,
                'romaji' => 'taberu',
                'inflections' => self::EXPECTED_INFLECTIONS['ichidan'],
            ],
            'code' => 'taberu',
        ],
        'katakana_2' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'katakana' => 'デモる',
                'hiragana' => null,
                'romaji' => 'demoru',
                'meaning' => [
                    'en' => ['to demonstrate (e.g. in the streets)'],
                ],
                'inflections' => self::EXPECTED_INFLECTIONS['katakana_2'],
            ],
            'code' => 'demoru',
        ],
        'kanji' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'kanji' => '作る',
                'romaji' => 'wakaru',
                'inflections' => self::EXPECTED_INFLECTIONS['godan'],
            ],
            'code' => 'wakaru',
        ],
        'group_ichidan' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['false_godan'],
                'group' => 'ichidan',
                'romaji' => 'akeru',
                'inflections' => self::EXPECTED_INFLECTIONS['group_ichidan'],
            ],
            'code' => 'akeru',
        ],
        'group_godan' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['false_ichidan'],
                'group' => 'godan',
                'romaji' => 'hairu',
                'inflections' => self::EXPECTED_INFLECTIONS['group_godan'],
            ],
            'code' => 'hairu',
        ],
        'meaning' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['ichidan'],
                'meaning' => [
                    'en' => ['to eat'],
                    'fr' => ['manger'],
                ],
            ],
            'code' => 'okiru',
        ],
        'romaji' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'romaji' => 'tsukuru',
            ],
            'code' => 'tsukuru',
        ],
        'dictionary' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'inflections' => self::EXPECTED_INFLECTIONS['dictionary'],
            ],
            'code' => 'wakaru',
        ],
        'inflections' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['ichidan'],
                'inflections' => self::EXPECTED_INFLECTIONS['inflections'],
            ],
            'code' => 'okiru',
        ],
        'dictionary_and_inflections' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['godan'],
                'inflections' => self::EXPECTED_INFLECTIONS[
                    'dictionary_and_inflections'],
            ],
            'code' => 'wakaru',
        ],
        'jlpt' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['ichidan'],
                'jlpt' => 4,
            ],
            'code' => 'okiru',
        ],
        'irregular' => [
            'doc' => [
                ...self::PUT_FIXTURE_VERBS['irregular'],
                'hiragana' => 'くる',
                'kanji' => '来る',
                'inflections' => self::EXPECTED_INFLECTIONS['irregular'],
            ],
            'code' => 'kuru',
        ],
    ];

    private const PUT_INVALID_VERBS = [
        'hiragana' => [
            'fixture' => 'ichidan',
            'payload' => [
                'hiragana' => 'オキル',
            ],
            'message' => 'hiragana: '.Verb::VALIDATION_ERR_HIRAGANA,
        ],
        'hiragana_maxlength' => [
            'fixture' => 'godan',
            'maxlength' => [
                'hiragana' => 'あ',
            ],
            'message' => [
                'text' => 'hiragana: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::HIRAGANA_MAXLENGTH,
            ],
        ],
        'katakana' => [
            'fixture' => 'godan',
            'payload' => [
                'katakana' => 'わかる',
            ],
            'message' => 'katakana: '.Verb::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_halfwidth' => [
            'fixture' => 'ichidan',
            'payload' => [
                'katakana' => 'ｵｷﾙ',
            ],
            'message' => 'katakana: '.Verb::VALIDATION_ERR_KATAKANA,
        ],
        'katakana_maxlength' => [
            'fixture' => 'ichidan',
            'maxlength' => [
                'katakana' => 'ア',
            ],
            'message' => [
                'text' => 'katakana: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::KATAKANA_MAXLENGTH,
            ],
        ],
        'no_hiragana_nor_katakana' => [
            'fixture' => 'ichidan',
            'payload' => [
                'hiragana' => '',
                'katakana' => '',
            ],
            'message' => 'hiragana: '.
                Verb::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA.
                PHP_EOL.
                'katakana: '.
                Verb::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
        ],
        'kanji' => [
            'fixture' => 'irregular',
            'payload' => [
                'kanji' => 'する',
            ],
            'message' => 'kanji: '.Verb::VALIDATION_ERR_KANJI,
        ],
        'kanji_maxlength' => [
            'fixture' => 'irregular',
            'maxlength' => [
                'kanji' => '日',
            ],
            'message' => [
                'text' => 'kanji: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::KANJI_MAXLENGTH,
            ],
        ],
        'romaji' => [
            'fixture' => 'ichidan',
            'payload' => [
                'romaji' => '起きる',
            ],
            'message' => 'romaji: '.Verb::VALIDATION_ERR_ROMAJI,
        ],
        'romaji_maxlength' => [
            'fixture' => 'irregular',
            'maxlength' => [
                'romaji' => 'r',
            ],
            'message' => [
                'text' => 'romaji: '.Verb::VALIDATION_ERR_MAXLENGTH,
                'values' => Verb::ROMAJI_MAXLENGTH,
            ],
        ],
        'group' => [
            'fixture' => 'irregular',
            'payload' => [
                'group' => 'dummy',
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_ENUM,
                'values' => Verb::ALLOWED_GROUPS,
            ],
        ],
        'group_invalid_ichidan' => [
            'fixture' => 'ichidan',
            'payload' => [
                'inflections' => [
                    'dictionary' => '飲む',
                ],
            ],
            'message' => 'group: '.Verb::VALIDATION_ERR_ICHIDAN,
        ],
        'group_invalid_godan' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [
                    'dictionary' => 'みず',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_GODAN,
                'values' => Verb::VALID_GODAN_ENDINGS,
            ],
        ],
        'group_invalid_irregular' => [
            'fixture' => 'irregular',
            'payload' => [
                'inflections' => [
                    'dictionary' => '食べる',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_IRREGULAR,
                'values' => Verb::IRREGULAR_VERBS,
            ],
        ],
        'group_invalid_is_irregular' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [
                    'dictionary' => '来る',
                ],
            ],
            'message' => [
                'text' => 'group: '.Verb::VALIDATION_ERR_IS_IRREGULAR,
                'values' => Verb::IRREGULAR_VERBS,
            ],
        ],
        'meaning_missing_mandatory_lang' => [
            'fixture' => 'godan',
            'payload' => [
                'meaning' => [
                    'fr' => ['comprendre'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[1],
                'values' => Verb::ALLOWED_LANGS[0],
            ],
        ],
        'meaning_unknown_lang' => [
            'fixture' => 'ichidan',
            'payload' => [
                'meaning' => [
                    'en' => ['to understand'],
                    'dummy' => ['わかる'],
                ],
            ],
            'message' => [
                'text' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[2],
                'values' => Verb::ALLOWED_LANGS,
            ],
        ],
        'meaning_type' => [
            'fixture' => 'irregular',
            'payload' => [
                'meaning' => [
                    'en' => 'to understand',
                ],
            ],
            'message' => 'meaning: '.Verb::VALIDATION_ERR_MEANING[3],
        ],
        'inflections' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [],
            ],
            'message' => 'inflections: '.Verb::VALIDATION_ERR_DICTIONARY,
        ],
        'inflections_empty_dictionary' => [
            'fixture' => 'godan',
            'payload' => [
                'inflections' => [
                    'dictionary' => '',
                ],
            ],
            'message' => 'inflections: '.Verb::VALIDATION_ERR_DICTIONARY,
        ],
        'jlpt_min' => [
            'fixture' => 'ichidan',
            'payload' => [
                'jlpt' => -9000,
            ],
            'message' => 'jlpt: '.Verb::VALIDATION_ERR_JLPT,
        ],
        'jlpt_max' => [
            'fixture' => 'godan',
            'payload' => [
                'jlpt' => 9000,
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

        foreach (self::PUT_VALID_VERBS as $key => $value) {
            ['fixture' => $fixture, 'payload' => $payload] = $value;
            $fixture = self::PUT_FIXTURE_VERBS[$fixture];
            $payload = array_merge($fixture, $payload);

            /** @var array<string,mixed> $doc */
            $doc = self::PUT_EXPECTED_VERBS[$key]['doc'];

            $expected = array_filter($doc, fn ($val) => !is_null($val));
            $code = self::PUT_EXPECTED_VERBS[$key]['code'];
            $provider[$key] = [$fixture, $payload, $expected, $code];
        }

        return $provider;
    }

    /**
     * @dataProvider validVerbsProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testVerbsPutValid(
        array $fixture,
        array $payload,
        array $expected,
        string $code,
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];
        $expectedIncrement = strstr(
            json_decode($response->getContent(), true)['code'],
            '-',
            true
        );

        // actual testing
        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Verb::class);

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('updatedAt', $content);
        $this->assertStringStartsWith(date('Y-m-d'), $content['updatedAt']);
        $this->assertSame($expectedIncrement.'-'.$code, $content['code']);
    }

    /**
     * @return array<array<array<string>>>
     */
    public function invalidVerbsProvider(): array
    {
        return $this->buildPutProvider(
            self::PUT_INVALID_VERBS,
            self::PUT_FIXTURE_VERBS
        );
    }

    /**
     * @dataProvider invalidVerbsProvider
     *
     * @param array<string> $fixture
     * @param array<string> $payload
     */
    public function testVerbsPutInvalid(
        array $fixture,
        array $payload,
        string $message
    ): void {
        // setting up fixture
        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            ['json' => $fixture]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        // actual testing
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $payload['@id'] = $_id;
        $response = static::createClient()->request(
            'PUT',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => $payload,
            ],
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        // needed to trigger the exception
        $content = json_decode($response->getContent(), true);
    }

    public function testVerbsPutUnknown(): void
    {
        static::createClient()->request(
            'PUT',
            'api/cards/verbs/dummy',
            [
                'json' => self::PUT_FIXTURE_VERBS['godan'],
            ]
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testVerbsPatchNotAllowed(): void
    {
        $response = static::createClient()->request(
            'POST',
            '/api/cards/verbs',
            [
                'json' => self::PUT_FIXTURE_VERBS['ichidan'],
            ]
        );
        $this->assertResponseStatusCodeSame(201);
        $_id = json_decode($response->getContent(), true)['@id'];

        static::createClient()->request(
            'PATCH',
            $_id,
            [
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => [
                    'romaji' => 'okiru',
                ],
            ],
        );
        $this->assertResponseStatusCodeSame(405);
    }
}
