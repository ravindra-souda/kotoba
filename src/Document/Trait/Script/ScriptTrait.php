<?php

declare(strict_types=1);

namespace App\Document\Trait\Script;

trait ScriptTrait
{
    private const HIRAGANA = 'hiragana';

    private const KATAKANA = 'katakana';

    private const ROMAJI = 'romaji';

    private const SHORT = [
        'hiragana' => [
            'か', 'き', 'く', 'け', 'こ',
            'た', 'ち', 'つ', 'て', 'と',
            'さ', 'し', 'す', 'せ', 'そ',
            'な', 'に', 'ぬ', 'ね', 'の',
            'は', 'ひ', 'ふ', 'へ', 'ほ',
            'ま', 'み', 'む', 'め', 'も',
            'や', 'ゆ', 'よ',
            'ら', 'り', 'る', 'れ', 'ろ',
            'が', 'ぎ', 'ぐ', 'げ', 'ご',
            'だ', 'ぢ', 'づ', 'で', 'ど',
            'ざ', 'じ', 'ず', 'ぜ', 'ぞ',
            'ば', 'び', 'ぶ', 'べ', 'ぼ',
            'ぱ', 'ぴ', 'ぷ', 'ぺ', 'ぽ',
            'ゃ', 'ゅ', 'ょ', '',
            'あ', 'い', 'う', 'え', 'お',
            'わ', 'を', 'ん', '、', '',
        ],
        'katakana' => [
            'カ', 'キ', 'ク', 'ケ', 'コ',
            'タ', 'チ', 'ツ', 'テ', 'ト',
            'サ', 'シ', 'ス', 'セ', 'ソ',
            'ナ', 'ニ', 'ヌ', 'ネ', 'ノ',
            'ハ', 'ヒ', 'フ', 'ヘ', 'ホ',
            'マ', 'ミ', 'ム', 'メ', 'モ',
            'ヤ', 'ユ', 'ヨ',
            'ラ', 'リ', 'ル', 'レ', 'ロ',
            'ガ', 'ギ', 'グ', 'ゲ', 'ゴ',
            'ダ', 'ヂ', 'ヅ', 'デ', 'ド',
            'ザ', 'ジ', 'ズ', 'ゼ', 'ゾ',
            'バ', 'ビ', 'ブ', 'ベ', 'ボ',
            'パ', 'ピ', 'プ', 'ペ', 'ポ',
            'ャ', 'ュ', 'ョ', '',
            'ア', 'イ', 'ウ', 'エ', 'オ',
            'ワ', 'ヲ', 'ン', '、', '',
        ],
        'romaji' => [
            'ka', 'ki', 'ku', 'ke', 'ko',
            'ta', 'chi', 'tsu', 'te', 'to',
            'sa', 'shi', 'su', 'se', 'so',
            'na', 'ni', 'nu', 'ne', 'no',
            'ha', 'hi', 'fu', 'he', 'ho',
            'ma', 'mi', 'mu', 'me', 'mo',
            'ya', 'yu', 'yo',
            'ra', 'ri', 'ru', 're', 'ro',
            'ga', 'gi', 'gu', 'ge', 'go',
            'da', 'di', 'du', 'de', 'do',
            'za', 'ji', 'zu', 'ze', 'zo',
            'ba', 'bi', 'bu', 'be', 'bo',
            'pa', 'pi', 'pu', 'pe', 'po',
            '%ya', '%yu', '%yo', '',
            'a', 'i', 'u', 'e', 'o',
            'wa', 'wo', 'n', ',', ' ',
        ]
    ];

    private const LONG = [
        'hiragana' => [
            'かあ', 'きい', 'くう', 'けい', 'こう',
            'たあ', 'ちい', 'つう', 'てい', 'とう',
            'さあ', 'しい', 'すう', 'せい', 'そう',
            'なあ', 'にい', 'ぬう', 'ねい', 'のう',
            'はあ', 'ひい', 'ふう', 'へい', 'ほう',
            'まあ', 'みい', 'むう', 'めい', 'もう',
            'やあ', 'ゆい', 'よう',
            'らあ', 'りい', 'るう', 'れい', 'ろう',
            'があ', 'ぎい', 'ぐう', 'げい', 'ごう',
            'だあ', 'ぢい', 'づう', 'でい', 'どう',
            'ざあ', 'じい', 'ずう', 'ぜい', 'ぞう',
            'ばあ', 'びい', 'ぶう', 'べい', 'ぼう',
            'ぱあ', 'ぴい', 'ぷう', 'ぺい', 'ぽう',
            'ゃあ', 'ゅう', 'ょう', '',
            'ああ', 'いい', 'うう', 'えい', 'おう',
            'わあ', 'をう',
        ],
        'katakana' => [
            'カー', 'キー', 'クー', 'ケー', 'コー',
            'ター', 'チー', 'ツー', 'テー', 'トー',
            'サー', 'シー', 'スー', 'セー', 'ソー',
            'ナー', 'ニー', 'ヌー', 'ネー', 'ノー',
            'ハー', 'ヒー', 'フー', 'ヘー', 'ホー',
            'マー', 'ミー', 'ムー', 'メー', 'モー',
            'ヤー', 'ユー', 'ヨー',
            'ラー', 'リー', 'ルー', 'レー', 'ロー',
            'ガー', 'ギー', 'グー', 'ゲー', 'ゴー',
            'ダー', 'ヂー', 'ヅー', 'デー', 'ドー',
            'ザー', 'ジー', 'ズー', 'ゼー', 'ゾー',
            'バー', 'ビー', 'ブー', 'ベー', 'ボー',
            'パー', 'ピー', 'プー', 'ペー', 'ポー',
            'ャー', 'ュー', 'ョー', '',
            'アー', 'イー', 'ウー', 'エー', 'オー',
            'ワー', 'ヲー',
        ],
        'romaji' => [
            'kaa', 'kii', 'kuu', 'kei', 'kou',
            'taa', 'chii', 'tsuu', 'tei', 'tou',
            'saa', 'shii', 'suu', 'sei', 'sou',
            'naa', 'nii', 'nuu', 'nei', 'nou',
            'haa', 'hii', 'fuu', 'hei', 'hou',
            'maa', 'mii', 'muu', 'mei', 'mou',
            'yaa', 'yuu', 'you',
            'raa', 'rii', 'ruu', 'rei', 'rou',
            'gaa', 'gii', 'guu', 'gei', 'gou',
            'daa', 'dii', 'duu', 'dei', 'dou',
            'zaa', 'jii', 'zuu', 'zei', 'zou',
            'baa', 'bii', 'buu', 'bei', 'bou',
            'paa', 'pii', 'puu', 'pei', 'pou',
            '%yaa', '%yuu', '%you', '',
            'aa', 'ii', 'uu', 'ei', 'ou',
            'waa', 'wou',
        ]
    ];

    private const HIRAGANA_LONG_RAW = [
        'あー', 'いー', 'うー', 'えー', 'おー',
        'かー', 'きー', 'くー', 'けー', 'こー',
        'さー', 'しー', 'すー', 'せー', 'そー',
        'たー', 'ちー', 'つー', 'てー', 'とー',
        'なー', 'にー', 'ぬー', 'ねー', 'のー',
        'はー', 'ひー', 'ふー', 'へー', 'ほー',
        'まー', 'みー', 'むー', 'めー', 'もー',
        'やー', 'ゆー', 'よー',
        'らー', 'りー', 'るー', 'れー', 'ろー',
        'わー', 'をー',
        'がー', 'ぎー', 'ぐー', 'げー', 'ごー',
        'ざー', 'じー', 'ずー', 'ぜー', 'ぞー',
        'だー', 'ぢー', 'づー', 'でー', 'どー',
        'ばー', 'びー', 'ぶー', 'べー', 'ぼー',
        'ぱー', 'ぴー', 'ぷー', 'ぺー', 'ぽー',
        'ゃー', 'ゅー', 'ょー', 'ー'
    ];

    private const HIRAGANA_LONG_FIXED = [
        'ああ', 'いい', 'うう', 'えい', 'おう',
        'かあ', 'きい', 'くう', 'けい', 'こう',
        'さあ', 'しい', 'すう', 'せい', 'そう',
        'たあ', 'ちい', 'つう', 'てい', 'とう',
        'なあ', 'にい', 'ぬう', 'ねい', 'のう',
        'はあ', 'ひい', 'ふう', 'へい', 'ほう',
        'まあ', 'みい', 'むう', 'めい', 'もう',
        'やあ', 'ゆい', 'よう',
        'らあ', 'りい', 'るう', 'れい', 'ろう',
        'わあ', 'をう',
        'があ', 'ぎい', 'ぐう', 'げい', 'ごう',
        'ざあ', 'じい', 'ずう', 'ぜい', 'ぞう',
        'だあ', 'ぢい', 'づう', 'でい', 'どう',
        'ばあ', 'びい', 'ぶう', 'べい', 'ぼう',
        'ぱあ', 'ぴい', 'ぷう', 'ぺい', 'ぽう',
        'ゃあ', 'ゅう', 'ょう', ''
    ];

    private const KATAKANA_LONG_RAW = [
        'カア', 'キイ', 'クウ', 'ケイ', 'コウ',
        'ガア', 'ギイ', 'グウ', 'ゲイ', 'ゴウ',
        'サア', 'シイ', 'スウ', 'セイ', 'ソウ',
        'ザア', 'ジイ', 'ズウ', 'ゼイ', 'ゾウ',
        'タア', 'チイ', 'ツウ', 'テイ', 'トウ',
        'ダア', 'ジイ', 'ヅウ', 'デイ', 'ドウ',
        'ナア', 'ニイ', 'ヌウ', 'ネイ', 'ノウ',
        'ハア', 'ヒイ', 'フウ', 'ヘイ', 'ホウ',
        'バア', 'ビイ', 'ブウ', 'ベイ', 'ボウ',
        'パア', 'ピイ', 'プウ', 'ペイ', 'ポウ',
        'マア', 'ミイ', 'ムウ', 'メイ', 'モウ',
        'ヤア', 'ユウ', 'ヨウ',
        'ャア', 'ュウ', 'ョウ',
        'ラア', 'リイ', 'ルウ', 'レイ', 'ロウ',
        'アア', 'イイ', 'ウウ', 'エイ', 'オウ',
    ];

    private const KATAKANA_LONG_FIXED = [
        'カー', 'キー', 'クー', 'ケー', 'コー',
        'ガー', 'ギー', 'グー', 'ゲー', 'ゴー',
        'サー', 'シー', 'スー', 'セー', 'ソー',
        'ザー', 'ジー', 'ズー', 'ゼー', 'ゾー',
        'ター', 'チー', 'ツー', 'テー', 'トー',
        'ダー', 'ジー', 'ヅー', 'デー', 'ドー',
        'ナー', 'ニー', 'ヌー', 'ネー', 'ノー',
        'ハー', 'ヒー', 'フー', 'ヘー', 'ホー',
        'バー', 'ビー', 'ブー', 'ベー', 'ボー',
        'パー', 'ピー', 'プー', 'ペー', 'ポー',
        'マー', 'ミー', 'ムー', 'メー', 'モー',
        'ヤー', 'ユー', 'ヨー',
        'ャー', 'ュー', 'ョー',
        'ラー', 'リー', 'ルー', 'レー', 'ロー',
        'アー', 'イー', 'ウー', 'エー', 'オー',
    ];

    private const CHIISAI_TSU = [
        'hiragana' => 'っ', 
        'katakana' => 'ッ', 
        'romaji' => '*'
    ];

    public static function toHiragana(?string $string): string|null|bool
    {
        if (empty($string)) {
            return null;
        }

        $hiragana = self::convert($string, self::HIRAGANA);

        return self::isHiragana($hiragana) ? $hiragana : false;
    }

    public static function toKatakana(?string $string): string|null|bool
    {
        if (empty($string)) {
            return null;
        }

        $katakana = self::convert($string, self::KATAKANA);

        return self::isKatakana($katakana) ? $katakana : false;
    }

    public static function toRomaji(?string $string): string|null|bool
    {
        if (empty($string)) {
            return null;
        }

        $romaji = self::convert($string, self::ROMAJI);

        return self::isRomaji($romaji) ? $romaji : false;
    }

    private static function convert(string $string, string $to): ?string
    {
        $from = self::detect($string);

        if ($from === $to) {
            return $string;
        }

        $string = self::convertChiisaiTsu($string, $from, $to);

        $string = str_replace(self::LONG[$from], self::LONG[$to], $string);
        $string = str_replace(self::SHORT[$from], self::SHORT[$to], $string);

        if ($from === self::KATAKANA) {
            $string = self::convertKatakanaLong($string, $to);
        }

        if ($to === self::KATAKANA) {
            return str_replace(
                self::KATAKANA_LONG_RAW, self::KATAKANA_LONG_FIXED, $string
            );
        }

        if ($to === self::ROMAJI) {
            $string = self::convertDoubleConsonants($string);
            return self::convertGlides($string);
        }

        return $string;
    }

    private static function convertChiisaiTsu(
        string $string, string $from, string $to
    ): string
    {
        $tsu = self::CHIISAI_TSU[$to];

        if ($from === self::KATAKANA) {
            return str_replace('ッ', $tsu, $string);
        }

        if ($from === self::HIRAGANA) {
            return str_replace('っ', $tsu, $string);
        }

        $search = [
            'kk', 'ss', 'tt', 'cc', 'hh', 'ff', 'mm', 'yy', 'rr',
            'gg', 'zz', 'jj', 'dd', 'bb', 'pp'
        ];
        $replacements = [];
        
        foreach ($search as $doubleConsonants) {
            $replacements[] = $tsu.substr($doubleConsonants, 1);
        }

        return str_replace($search, $replacements, $string);
    }

    private static function convertDoubleConsonants(string $string): string
    {
        $offset = strpos($string, '*');
        while ($offset !== false) {
            $replacement = substr($string, $offset + 1, 1);
            $string = substr_replace($string, $replacement, $offset, 1);
            $offset = strpos($string, '*', $offset);
        }

        return $string;
    }

    private static function convertGlides(string $string): string
    {
        $offset = strpos($string, '%');
        while ($offset !== false) {            
            $previousKana = substr($string, $offset - 3, 3);
            $charsToDelete = in_array($previousKana, ['shi', 'chi']) ? 3 : 2;
            $string = substr_replace($string, '', $offset - 1, $charsToDelete);
            $offset = strpos($string, '%', $offset);
        }

        return $string;
    }

    private static function convertKatakanaLong(
        string $string, string $to
    ): string
    {
        if ($to === self::ROMAJI) {
            $search = ['aー', 'iー', 'uー', 'eー', 'oー'];
            $replacements = ['aa', 'ii', 'uu', 'ei', 'ou'];

            return str_replace($search, $replacements, $string);
        }

        return str_replace(
            self::HIRAGANA_LONG_RAW, self::HIRAGANA_LONG_FIXED, $string
        );
    }

    private static function detect(string $string): string
    {
        if (self::isHiragana($string)) {
            return self::HIRAGANA;
        }

        if (self::isKatakana($string)) {
            return self::KATAKANA;
        }

        return self::ROMAJI;
    }

    private static function isHiragana(string $string): bool
    {
        return preg_match('/^\p{Hiragana}+$/um', $string) === 1;
    }

    private static function isKatakana(string $string): bool
    {
        return preg_match('/^\p{Katakana}+$/um', $string) === 1;
    }

    private static function isRomaji(string $string): bool
    {
        return preg_match('/^[a-z,]+$/um', $string) === 1;
    }
}
