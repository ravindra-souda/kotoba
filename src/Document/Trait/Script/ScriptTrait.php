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
            'かあ', 'きい', 'くう', 'けえ', 'こお',
            'たあ', 'ちい', 'つう', 'てえ', 'とお',
            'さあ', 'しい', 'すう', 'せえ', 'そお',
            'なあ', 'にい', 'ぬう', 'ねえ', 'のお',
            'はあ', 'ひい', 'ふう', 'へえ', 'ほお',
            'まあ', 'みい', 'むう', 'めえ', 'もお',
            'やあ', 'ゆう', 'よお',
            'らあ', 'りい', 'るう', 'れえ', 'ろお',
            'があ', 'ぎい', 'ぐう', 'げえ', 'ごお',
            'だあ', 'ぢい', 'づう', 'でえ', 'どお',
            'ざあ', 'じい', 'ずう', 'ぜえ', 'ぞお',
            'ばあ', 'びい', 'ぶう', 'べえ', 'ぼお',
            'ぱあ', 'ぴい', 'ぷう', 'ぺえ', 'ぽお',
            'ゃあ', 'ゅう', 'ょお', '',
            'ああ', 'いい', 'うう', 'ええ', 'おお',
            'わあ', 'をお',
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
            'kā', 'kii', 'kū', 'kē', 'kō',
            'tā', 'chii', 'tsū', 'tē', 'tō',
            'sā', 'shii', 'sū', 'sē', 'sō',
            'nā', 'nii', 'nū', 'nē', 'nō',
            'hā', 'hii', 'fū', 'hē', 'hō',
            'mā', 'mii', 'mū', 'mē', 'mō',
            'yā', 'yū', 'yō',
            'rā', 'rii', 'rū', 'rē', 'rō',
            'gā', 'gii', 'gū', 'gē', 'gō',
            'dā', 'dii', 'dū', 'dē', 'dō',
            'zā', 'jii', 'zū', 'zē', 'zō',
            'bā', 'bii', 'bū', 'bē', 'bō',
            'pā', 'pii', 'pū', 'pē', 'pō',
            '%yā', '%yū', '%yō', '',
            'ā', 'ii', 'ū', 'ē', 'ō',
            'wā', 'wō',
        ]
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
        return preg_match('/^[a-z,āūēō]+$/um', $string) === 1;
    }
}
