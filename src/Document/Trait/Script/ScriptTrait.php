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
        ],
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
        ],
    ];

    private const GLIDES_TO_FIX = ['shy', 'chy', 'dy', 'jy'];

    private const GLIDES_FIXED = ['sh', 'ch', 'dj', 'j'];

    private const GLIDES_ROMAJI = [
        'romaji' => [
            'kya', 'kyu', 'kyo', 'kyā', 'kyū', 'kyō',
            'sha', 'shu', 'sho', 'shā', 'shū', 'shō',
            'cha', 'chu', 'cho', 'chā', 'chū', 'chō',
            'nya', 'nyu', 'nyo', 'nyā', 'nyū', 'nyō',
            'hya', 'hyu', 'hyo', 'hyā', 'hyū', 'hyō',
            'mya', 'myu', 'myo', 'myā', 'myū', 'myō',
            'rya', 'ryu', 'ryo', 'ryā', 'ryū', 'ryō',
            'gya', 'gyu', 'gyo', 'gyā', 'gyū', 'gyō',
            'dja', 'dju', 'djo', 'djā', 'djū', 'djō',
            'ja', 'ju', 'jo', 'jā', 'jū', 'jō',
            'bya', 'byu', 'byo', 'byā', 'byū', 'byō',
            'pya', 'pyu', 'pyo', 'pyā', 'pyū', 'pyō',
        ],
        'hiragana' => [
            'きゃ', 'きゅ', 'きょ', 'きゃあ', 'きゅう', 'きょお',
            'しゃ', 'しゅ', 'しょ', 'しゃあ', 'しゅう', 'しょお',
            'ちゃ', 'ちゅ', 'ちょ', 'ちゃあ', 'ちゅう', 'ちょお',
            'にゃ', 'にゅ', 'にょ', 'にゃあ', 'にゅう', 'にょお',
            'ひゃ', 'ひゅ', 'ひょ', 'ひゃあ', 'ひゅう', 'ひょお',
            'みゃ', 'みゅ', 'みょ', 'みゃあ', 'みゅう', 'みょお',
            'りゃ', 'りゅ', 'りょ', 'りゃあ', 'りゅう', 'りょお',
            'ぎゃ', 'ぎゅ', 'ぎょ', 'ぎゃあ', 'ぎゅう', 'ぎょお',
            'ぢゃ', 'ぢゅ', 'ぢょ', 'ぢゃあ', 'ぢゅう', 'ぢょお',
            'じゃ', 'じゅ', 'じょ', 'じゃあ', 'じゅう', 'じょお',
            'びゃ', 'びゅ', 'びょ', 'びゃあ', 'びゅう', 'びょお',
            'ぴゃ', 'ぴゅ', 'ぴょ', 'ぴゃあ', 'ぴゅう', 'ぴょお',
        ],
        'katakana' => [
            'キャ', 'キュ', 'キョ', 'キャー', 'キュー', 'キョー',
            'シャ', 'シュ', 'ショ', 'シャー', 'シュー', 'ショー',
            'チャ', 'チュ', 'チョ', 'チャー', 'チュー', 'チョー',
            'ニャ', 'ニュ', 'ニョ', 'ニャー', 'ニュー', 'ニョー',
            'ヒャ', 'ヒュ', 'ヒョ', 'ヒャー', 'ヒュー', 'ヒョー',
            'ミャ', 'ミュ', 'ミョ', 'ミャー', 'ミュー', 'ミョー',
            'リャ', 'リュ', 'リョ', 'リャー', 'リュー', 'リョー',
            'ギャ', 'ギュ', 'ギョ', 'ギャー', 'ギュー', 'ギョー',
            'ヂャ', 'ヂュ', 'ヂョ', 'ヂャー', 'ヂュー', 'ヂョー',
            'ジャ', 'ジュ', 'ジョ', 'ジャー', 'ジュー', 'ジョー',
            'ビャ', 'ビュ', 'ビョ', 'ビャー', 'ビュー', 'ビョー',
            'ピャ', 'ピュ', 'ピョ', 'ピャー', 'ピュー', 'ピョー',
        ],
    ];

    private const CHIISAI_TSU = [
        'hiragana' => 'っ',
        'katakana' => 'ッ',
        'romaji' => '*',
    ];

    public static function toHiragana(?string $string): string|null|bool
    {
        if (empty($string)) {
            return null;
        }

        $hiragana = self::convert($string, self::HIRAGANA);

        return self::isHiragana($hiragana) ? $hiragana : false;
    }

    public static function toKatakana(
        ?string $string,
        bool $longKatakana = true
    ): string|null|bool {
        if (empty($string)) {
            return null;
        }

        $katakana = self::convert($string, self::KATAKANA, $longKatakana);

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

    private static function convert(
        string $string,
        string $to,
        bool $longKatakana = true
    ): string {
        $from = self::detect($string);

        if ($from === $to) {
            return $string;
        }

        $string = self::convertChiisaiTsu($string, $from, $to);

        if (self::ROMAJI === $from && !$longKatakana) {
            $string = str_replace(
                ['ā', 'ū', 'ē', 'ō'],
                ['aa', 'uu', 'ee', 'oo'],
                $string
            );
        }

        if (self::ROMAJI === $from) {
            $string = str_replace(
                self::GLIDES_ROMAJI['romaji'],
                self::GLIDES_ROMAJI[$to],
                $string
            );
        }

        if ($longKatakana) {
            $string = str_replace(self::LONG[$from], self::LONG[$to], $string);
        }
        $string = str_replace(self::SHORT[$from], self::SHORT[$to], $string);

        if (self::ROMAJI === $to) {
            $string = self::convertDoubleConsonants($string);

            return self::convertGlides($string);
        }

        return $string;
    }

    private static function convertChiisaiTsu(
        string $string,
        string $from,
        string $to
    ): string {
        $tsu = self::CHIISAI_TSU[$to];

        if (self::KATAKANA === $from) {
            return str_replace('ッ', $tsu, $string);
        }

        if (self::HIRAGANA === $from) {
            return str_replace('っ', $tsu, $string);
        }

        $search = [
            'kk', 'ss', 'tt', 'hh', 'ff', 'mm', 'yy', 'rr',
            'gg', 'zz', 'jj', 'dd', 'bb', 'pp', 'tch',
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
        while (false !== $offset) {
            $replacement = substr($string, $offset + 1, 1);
            $string = substr_replace($string, $replacement, $offset, 1);
            $offset = strpos($string, '*', $offset);
        }

        return str_replace('cch', 'tch', $string);
    }

    private static function convertGlides(string $string): string
    {
        $offset = strpos($string, '%');
        while (false !== $offset) {
            $string = substr_replace($string, '', $offset - 1, 2);
            $offset = strpos($string, '%', $offset);
        }

        return str_replace(self::GLIDES_TO_FIX, self::GLIDES_FIXED, $string);
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
        return 1 === preg_match('/^\p{Hiragana}+$/um', $string);
    }

    private static function isKatakana(string $string): bool
    {
        return 1 === preg_match('/^\p{Katakana}+$/um', $string);
    }

    private static function isRomaji(string $string): bool
    {
        return 1 === preg_match('/^[a-z,āūēō ]+$/um', $string);
    }
}
