<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Document\Card;
use App\Document\Noun;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @internal
 *
 * @coversNothing
 */
class CardsPostTest extends ApiTestCase
{
    private string $romajiMaxlengthString;

    private string $hiraganaMaxlengthString;

    private string $katakanaMaxlengthString;
    
    private string $kanjiMaxlengthString;
    
    public function __construct()
    {
        $this->romajiMaxlengthString =
            str_repeat('a', Noun::ROMAJI_MAXLENGTH + 1);
        $this->hiraganaMaxlengthString = 
            str_repeat('あ', Noun::HIRAGANA_MAXLENGTH + 1);
        $this->katakanaMaxlengthString = 
            str_repeat('ア', Noun::KATAKANA_MAXLENGTH + 1);
        $this->kanjiMaxlengthString = 
            str_repeat('字', Noun::KANJI_MAXLENGTH + 1);
    }

    private const POST_COMPLETE_VALID_CARDS = [
        'hiragana' => [
            'type' => 'noun',
            'romaji' => '  gakKou       ',
            'hiragana' => '   がっこう ',
            'katakana' => '',
            'kanji' => '学校',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' schoOl',
                'fr' => 'école ',
            ],
        ],
        'katakana' => [
            'type' => 'noun',
            'romaji' => '    Neko     ',
            'hiragana' => '',
            'katakana' => '    ネコ ',
            'kanji' => ' 猫  ',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' cat  ',
                'fr' => ' cHat ',
            ],
        ],
        'teneigo' => [
            'type' => 'noun',
            'romaji' => ' oKane ',
            'hiragana' => ' おかね',
            'katakana' => '',
            'kanji' => 'お金',
            'bikago' => 'お',
            'jlpt' => 5,
            'meaning' => [
                'en' => ' money   ',
            ],
        ],
        'godan' => [
            'type' => 'verb',
            'romaji' => 'iku',
            'hiragana' => 'いく',
            'kanji' => '行く',
            'jlpt' => 5,
            'group' => 'godan',
            'meaning' => [
                'en' => 'to go',
            ],
            'conj' => [
                'dictionary' => '行く',
            ],
        ],
        'ichidan' => [
            'type' => 'verb',
            'romaji' => 'taberu',
            'hiragana' => 'たべる',
            'kanji' => '食べる',
            'jlpt' => 5,
            'group' => 'ichidan',
            'meaning' => [
                'en' => 'to eat',
            ],
            'conj' => [
                'dictionary' => '食べる',
            ],
        ],
        'irregular' => [
            'type' => 'verb',
            'romaji' => 'kuru',
            'hiragana' => 'くる',
            'kanji' => '来る',
            'jlpt' => 5,
            'group' => 'irregular',
            'meaning' => [
                'en' => 'to come',
            ],
            'conj' => [
                'dictionary' => '来る',
            ],
        ],
        'i_adjective' => [
            'type' => 'adjective',
            'romaji' => 'kawaii',
            'hiragana' => 'かわいい',
            'kanji' => '可愛い',
            'jlpt' => 5,
            'group' => 'i',
            'meaning' => [
                'en' => 'cute, adorable, charming, lovely, pretty',
            ],
        ],
        'na_adjective' => [
            'type' => 'adjective',
            'romaji' => 'kirei',
            'hiragana' => 'きれい',
            'kanji' => '綺麗',
            'jlpt' => 5,
            'group' => 'na',
            'meaning' => [
                'en' => [
                    'pretty, lovely, beautiful, fair',
                    'clean, clear, pure, tidy, neat',
                ],
            ],
        ],
        'kana_hiragana' => [
            'type' => 'kana',
            'romaji' => 'a',
            'hiragana' => 'あ',
        ],
        'kana_katakana' => [
            'type' => 'kana',
            'romaji' => 'a',
            'katakana' => 'ア',
        ],
        'kana_hiragana_glide' => [
            'type' => 'kana',
            'romaji' => 'kya',
            'hiragana' => 'きゃ',
        ],
        'kana_katakana_glide' => [
            'type' => 'kana',
            'romaji' => 'kya',
            'katakana' => 'キャ',
        ],
        'kanji' => [
            'type' => 'kanji',
            'kanji' => '人',
            'meaning' => [
                'en' => 'person',
                'fr' => 'personne, humain'
            ],
            'kunyomi' => 'hito, hitori, hitoto',
            'onyomi' => 'jin, nin',
        ],
    ];
    private const POST_COMPLETE_EXPECTED_CARDS = [
        'hiragana' => [
            ...self::POST_COMPLETE_VALID_CARDS['hiragana'],
            'romaji' => 'gakkou',
            'hiragana' => 'がっこう',
            'meaning' => [
                'en' => 'school',
                'fr' => 'école',
            ],
        ],
        'katakana' => [
            ...self::POST_COMPLETE_VALID_CARDS['katakana'],
            'romaji' => 'neko',
            'katakana' => 'ネコ',
            'kanji' => '猫',
            'meaning' => [
                'en' => 'cat',
                'fr' => 'chat',
            ],
        ],
        'teneigo' => [
            ...self::POST_COMPLETE_VALID_CARDS['teneigo'],
            'romaji' => 'okane',
            'hiragana' => 'おかね',
            'meaning' => [
                'en' => 'money',
            ],
        ],
        'godan' => [
            ...self::POST_COMPLETE_VALID_CARDS['godan'],
            'conj' => [
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
            ...self::POST_COMPLETE_VALID_CARDS['ichidan'],
            'conj' => [
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
            ...self::POST_COMPLETE_VALID_CARDS['ichidan'],
            /* automatic conjugation must be disabled for irregular verbs,
            leaving the completion to the user */
            'conj' => [
                'dictionary' => '来る',
            ],
        ],
        'i_adjective' => [
            ...self::POST_COMPLETE_VALID_CARDS['i_adjective'],
            'conj' => [
                'non-past' => [
                    'affirmative' => '可愛い',
                    'negative' => '可愛くない',
                ],
                'past' => [
                    'affirmative' => '可愛かった',
                    'negative' => '可愛くなかった',
                ],
            ],
        ],
        'na_adjective' => [
            ...self::POST_COMPLETE_VALID_CARDS['na_adjective'],
            'conj' => [
                'non-past' => [
                    'affirmative' => '綺麗',
                    'negative' => '綺麗じゃない',
                ],
                'past' => [
                    'affirmative' => '綺麗だった',
                    'negative' => '綺麗じゃなかった',
                ],
            ],
        ],
    ];
    private const POST_MINIMAL_VALID_CARD = [
        'type' => 'verb',
        'romaji' => 'taberu',
        'hiragana' => 'たべる',
        'group' => 'ichidan',
        'meaning' => [
            'en' => 'to eat',
        ]
    ];

    private const POST_INVALID_CARDS = [
        'romaji_empty' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'romaji' => '',
        ],
        'romaji_maxlength' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'romaji' => '*',
        ],
        'romaji_written_in_kana' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'romaji' => 'ローマジ',
        ],
        'no_hiragana_nor_katakana' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'hiragana' => '',
            'katakana' => '',
        ],
        'hiragana_written_in_katakana' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'hiragana' => 'カタカナ',
        ],
        'hiragana_maxlength' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'hiragana' => '*',
        ],
        'katakana_written_in_hiragana' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'katakana' => 'ひらがな',
        ],
        'katakana_maxlength' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'katakana' => '*',
        ],
        'kanji_maxlength' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'kanji' => '*',
        ],
        'kanji_written_in_romaji' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'kanji' => 'kanji',
        ],
        'bikago' => [
            ...self::POST_COMPLETE_VALID_CARDS['teneigo'],
            'bikago' => 'dummy',
        ],
        'type' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'type' => 'dummy',
        ],
        'meaning_empty' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'meaning' => '',
        ],
        'meaning_not_an_array' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'meaning' => 'to eat',
        ],
        'meaning_lang_unknown' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'meaning' => [
                'en' => 'to eat',
                'dummy' => '🂡🂱🃁🃑',
            ],
        ],
        'group_verb' => [
            ...self::POST_COMPLETE_VALID_CARDS['godan'],
            'group' => 'i',
        ],
        'group_adjective' => [
            ...self::POST_COMPLETE_VALID_CARDS['i_adjective'],
            'group' => 'godan',
        ],
        'jlpt_not_an_integer' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'jlpt' => 1.1,
        ],
        'jlpt_min' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'jlpt' => 0,
        ],
        'jlpt_max' => [
            ...self::POST_MINIMAL_VALID_CARD,
            'jlpt' => 6,
        ],
        'kana_hiragana' => [
            ...self::POST_COMPLETE_VALID_CARDS['kana_hiragana'],
            'hiragana' => 'いい',
        ],
        'kana_katakana' => [
            ...self::POST_COMPLETE_VALID_CARDS['kana_katakana'],
            'katakana' => 'アア',
        ],
        'kana_hiragana_glide' => [
            ...self::POST_COMPLETE_VALID_CARDS['kana_hiragana_glide'],
            'hiragana' => 'きゃあ',
        ],
        'kana_katakana_glide' => [
            ...self::POST_COMPLETE_VALID_CARDS['kana_katakana_glide'],
            'katakana' => 'キャア',
        ],
        'kanji_kanji_maxlength' => [
            ...self::POST_COMPLETE_VALID_CARDS['kanji'],
            'kanji' => '一人',
        ],
        'kanji_kunyomi_empty' => [
            ...self::POST_COMPLETE_VALID_CARDS['kanji'],
            'kunyomi' => '',
        ],
        'kanji_kunyomi_not_in_romaji' => [
            ...self::POST_COMPLETE_VALID_CARDS['kanji'],
            'kunyomi' => 'ひと, ひとり, ひとと',
        ],
        'kanji_onyomi_empty' => [
            ...self::POST_COMPLETE_VALID_CARDS['kanji'],
            'onyomi' => '',
        ],
        'kanji_onyomi_not_in_romaji' => [
            ...self::POST_COMPLETE_VALID_CARDS['kanji'],
            'kunyomi' => 'ジン, ニン',
        ],
    ];

    private const UNIQUE_INCREMENT_DECKS = [
        ['title' => 'to be deleted'],
        ['title' => 'unique increment 1'],
        ['title' => 'unique increment 2'],
    ];

    private const CLASSES = [
        'adjective' => Adjective::class,
        'kana' => Kana::class,
        'kanji' => Kanji::class,
        'noun' => Noun::class,
        'verb' => Verb::class,
    ];

    /**
     * @return array<array<array<string>>>
     */
    public function validCardProvider(): array
    {
        $provider = [];
        array_walk(self::POST_COMPLETE_VALID_CARDS, function($value, $key) {
            $expected = isset(self::POST_COMPLETE_EXPECTED_CARDS[$key]) ?
                self::POST_COMPLETE_EXPECTED_CARDS[$key] : $value;
            $provider[] = [$value, $expected];
        });

        return $provider;
    }

    /**
     * @dataProvider validCardProvider
     *
     * @param array<string> $payload
     * @param array<string> $expected
     */
    public function testCardsPostValid(
        array $payload,
        array $expected,
    ): void {
        $response = static::createClient()->request(
            'POST',
            '/api/cards',
            ['json' => $payload]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains($expected);
        $this->assertMatchesResourceItemJsonSchema(Card::class);
        $this->assertMatchesResourceItemJsonSchema(
            self::CLASSES[$expected['type']]
        );

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
    public function invalidCardProvider(): array
    {
        return [
            [
                self::POST_INVALID_CARDS['romaji_empty'],
                'romaji: '.Card::VALIDATION_ERR_EMPTY,
            ],
            [
                [
                    ...self::POST_INVALID_CARDS['romaji_maxlength'],
                    'romaji' => $romajiMaxlengthString,
                ],
                'romaji: '.Card::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_CARDS['romaji_written_in_kana'],
                'romaji: '.Card::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_CARDS['no_hiragana_nor_katakana'],
                'hiragana, katakana: '.
                Card::VALIDATION_ERR_NO_HIRAGANA_NOR_KATAKANA,
            ],
            [
                self::POST_INVALID_CARDS['hiragana_written_in_katakana'],
                'hiragana: '.Card::VALIDATION_ERR_HIRAGANA,
            ],
            [
                [
                    ...self::POST_INVALID_CARDS['hiragana_maxlength'],
                    'hiragana' => $hiraganaMaxlengthString,
                ],
                'hiragana: '.Card::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_CARDS['katakana_written_in_hiragana'],
                'katakana: '.Card::VALIDATION_ERR_KATAKANA,
            ],
            [
                [
                    ...self::POST_INVALID_CARDS['katakana_maxlength'],
                    'katakana' => $katakanaMaxlengthString,
                ],
                'katakana: '.Card::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                [
                    ...self::POST_INVALID_CARDS['kanji_maxlength'],
                    'kanji' => $kanjiMaxlengthString,
                ],
                'kanji: '.Card::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_CARDS['kanji_written_in_romaji'],
                'kanji: '.Card::VALIDATION_ERR_KANJI,
            ],
            [
                self::POST_INVALID_CARDS['teneigo'],
                'bikago: '.Card::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_CARDS['type'],
                'type: '.Card::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_CARDS['meaning_empty'],
                'meaning: '.Card::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_CARDS['meaning_not_an_array'],
                'meaning: '.Card::VALIDATION_ERR_NOT_AN_ARRAY,
            ],
            [
                self::POST_INVALID_CARDS['meaning_lang_unknown'],
                'meaning: '.Card::VALIDATION_ERR_MEANING,
            ],
            [
                self::POST_INVALID_CARDS['group_verb'],
                'group: '.Verb::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_CARDS['group_adjective'],
                'group: '.Adjective::VALIDATION_ERR_ENUM,
            ],
            [
                self::POST_INVALID_CARDS['jlpt_not_an_integer'],
                'jlpt: '.Card::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_CARDS['jlpt_min'],
                'jlpt: '.Card::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_CARDS['jlpt_max'],
                'jlpt: '.Card::VALIDATION_ERR_JLPT,
            ],
            [
                self::POST_INVALID_CARDS['kana_hiragana'],
                'hiragana: '.Kana::VALIDATION_ERR_KANA,
            ],
            [
                self::POST_INVALID_CARDS['kana_katakana'],
                'katakana: '.Kana::VALIDATION_ERR_KANA,
            ],
            [
                self::POST_INVALID_CARDS['kana_hiragana_glide'],
                'hiragana: '.Kana::VALIDATION_ERR_KANA,
            ],
            [
                self::POST_INVALID_CARDS['kana_katakana_glide'],
                'katakana: '.Kana::VALIDATION_ERR_KANA,
            ],
            [
                self::POST_INVALID_CARDS['kanji_kanji_maxlength'],
                'kanji: '.Kanji::VALIDATION_ERR_MAXLENGTH,
            ],
            [
                self::POST_INVALID_CARDS['kanji_kunyomi_empty'],
                'kunyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_CARDS['kanji_kunyomi_not_in_romaji'],
                'kunyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_CARDS['kanji_onyomi_empty'],
                'onyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
            [
                self::POST_INVALID_CARDS['kanji_onyomi_not_in_romaji'],
                'onyomi: '.Kanji::VALIDATION_ERR_ROMAJI,
            ],
        ];
    }

    /**
     * @dataProvider invalidCardProvider
     *
     * @param array<string> $payload
     */
    public function testCardsPostInvalid(array $payload, string $message): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($message);

        $response = static::createClient()->request(
            'POST',
            '/api/cards',
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

    public function testGeneratedIncrementMustBeUnique(): void
    {
        $increments = [];
        foreach (array_slice(self::UNIQUE_INCREMENT_DECKS, 0, 2) as $deck) {
            $response = static::createClient()->request(
                'POST',
                '/api/decks',
                ['json' => $deck]
            );
            $this->assertResponseStatusCodeSame(201);
            $increments[] = strstr(
                json_decode($response->getContent(), true)['code'],
                '-',
                true
            );
            if (!isset($_id)) {
                $_id = json_decode(
                    $response->getContent(),
                    true
                )['@id'];
            }
        }

        static::createClient()->request(
            'DELETE',
            $_id,
        );
        $this->assertResponseStatusCodeSame(204);

        $response = static::createClient()->request(
            'POST',
            '/api/decks',
            ['json' => self::UNIQUE_INCREMENT_DECKS[2]]
        );
        $this->assertResponseStatusCodeSame(201);

        $increments[] = strstr(
            json_decode($response->getContent(), true)['code'],
            '-',
            true
        );
        $this->assertSame($increments, array_unique($increments));
    }
}
