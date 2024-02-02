<?php

declare(strict_types=1);

namespace App\Document\Trait\Script;

trait ScriptTrait
{
    private const ROMAJI = [
        'kkya', 'kkyu', 'kkyo',
        'kka', 'kki', 'kku', 'kke', 'kko',
        'kya', 'kyu', 'kyo',
        'ka', 'ki', 'ku', 'ke', 'ko',
        
        'ggya', 'ggyu', 'ggyo',
        'gga', 'ggi', 'ggu', 'gge', 'ggo',
        'gya', 'gyu', 'gyo',
        'ga', 'gi', 'gu', 'ge', 'go',
        
        'ccha', 'cchu', 'ccho',
        'tta', 'cchi', 'ttsu', 'tte', 'tto',
        'cha', 'chu', 'cho',
        'ta', 'chi', 'tsu', 'te', 'to',

        'ddya', 'ddyu', 'ddyo',
        'dda', 'ddi', 'ddu', 'dde', 'ddo',
        'dya', 'dyu', 'dyo',
        'da', 'di', 'du', 'de', 'do',

        'ssha', 'sshu', 'ssho',
        'ssa', 'sshi', 'ssu', 'sse', 'sso',
        'sha', 'shu', 'sho',
        'sa', 'shi', 'su', 'se', 'so',
        
        'jjya', 'jjyu', 'jjyo',
        'zza', 'jji', 'zzu', 'zze', 'zzo',
        'jya', 'jyu', 'jyo',
        'za', 'ji', 'zu', 'ze', 'zo',
        
        'nnya', 'nnyu', 'nnyo',
        'nna', 'nni', 'nnu', 'nne', 'nno',
        'nya', 'nyu', 'nyo',
        'na', 'ni', 'nu', 'ne', 'no',
        
        'hhya', 'hhyu', 'hhyo',
        'hha', 'hhi', 'ffu', 'hhe', 'hho',
        'hya', 'hyu', 'hyo',
        'ha', 'hi', 'fu', 'he', 'ho',
        
        'bbya', 'bbyu', 'bbyo',
        'bba', 'bbi', 'bbu', 'bbe', 'bbo',
        'bya', 'byu', 'byo',
        'ba', 'bi', 'bu', 'be', 'bo',
        
        'ppya', 'ppyu', 'ppyo',
        'ppa', 'ppi', 'ppu', 'ppe', 'ppo',
        'pya', 'pyu', 'pyo',
        'pa', 'pi', 'pu', 'pe', 'po',
        
        'mmya', 'mmyu', 'mmyo',
        'mma', 'mmi', 'mmu', 'mme', 'mmo',
        'mya', 'myu', 'myo',
        'ma', 'mi', 'mu', 'me', 'mo',
        
        'rrya', 'rryu', 'rryo',
        'rra', 'rri', 'rru', 'rre', 'rro',
        'rya', 'ryu', 'ryo',
        'ra', 'ri', 'ru', 're', 'ro',

        'yya', 'yyu', 'yyo',
        'ya', 'yu', 'yo',
        
        'wa', 'wo', 'n',
        'a', 'i', 'u', 'e', 'o',
        ' ', ',',
    ];

    private const HIRAGANA = [
        'っきゃ', 'っきゅ', 'っきょ',
        'っか', 'っき', 'っく', 'っけ', 'っこ',
        'きゃ', 'きゅ', 'きょ',
        'か', 'き', 'く', 'け', 'こ',
        
        'っぎゃ', 'っぎゅ', 'っぎょ',
        'っが', 'っぎ', 'っぐ', 'っげ', 'っご',
        'ぎゃ', 'ぎゅ', 'ぎょ',
        'が', 'ぎ', 'ぐ', 'げ', 'ご',
        
        'っちゃ', 'っちゅ', 'っちょ',
        'った', 'っち', 'っつ', 'って', 'っと',
        'ちゃ', 'ちゅ', 'ちょ',
        'た', 'ち', 'つ', 'て', 'と',

        'っぢゃ', 'っぢゅ', 'っぢょ',
        'っだ', 'っぢ', 'っづ', 'っで', 'っど',
        'ぢゃ', 'ぢゅ', 'ぢょ',
        'だ', 'ぢ', 'づ', 'で', 'ど',

        'っしゃ', 'っしゅ', 'っしょ',
        'っさ', 'っし', 'っす', 'っせ', 'っそ',
        'しゃ', 'しゅ', 'しょ',
        'さ', 'し', 'す', 'せ', 'そ',
        
        'っじゃ', 'っじゅ', 'っじょ',
        'っざ', 'っじ', 'っず', 'っぜ', 'っぞ',
        'じゃ', 'じゅ', 'じょ',
        'ざ', 'じ', 'ず', 'ぜ', 'ぞ',
        
        'っにゃ', 'っにゅ', 'っにょ',
        'っな', 'っに', 'っぬ', 'っね', 'っの',
        'にゃ', 'にゅ', 'にょ',
        'な', 'に', 'ぬ', 'ね', 'の',
        
        'っひゃ', 'っひゅ', 'っひょ',
        'っは', 'っひ', 'っふ', 'っへ', 'っほ',
        'ひゃ', 'ひゅ', 'ひょ',
        'は', 'ひ', 'ふ', 'へ', 'ほ',
        
        'っびゃ', 'っびゅ', 'っびょ',
        'っば', 'っび', 'っぶ', 'っべ', 'っぼ',
        'びゃ', 'びゅ', 'びょ',
        'ば', 'び', 'ぶ', 'べ', 'ぼ',
        
        'っぴゃ', 'っぴゅ', 'っぴょ',
        'っぱ', 'っぴ', 'っぷ', 'っぺ', 'っぽ',
        'ぴゃ', 'ぴゅ', 'ぴょ',
        'ぱ', 'ぴ', 'ぷ', 'ぺ', 'ぽ',
        
        'っみゃ', 'っみゅ', 'っみょ',
        'っま', 'っみ', 'っむ', 'っめ', 'っも',
        'みゃ', 'みゅ', 'みょ',
        'ま', 'み', 'む', 'め', 'も',
        
        'っりゃ', 'っりゅ', 'っりょ',
        'っら', 'っり', 'っる', 'っれ', 'っろ',
        'りゃ', 'りゅ', 'りょ',
        'ら', 'り', 'る', 'れ', 'ろ',

        'っや', 'っゆ', 'っよ',
        'や', 'ゆ', 'よ',
        
        'わ', 'を', 'ん',
        'あ', 'い', 'う', 'え', 'お',
        '', '、'
    ];

    private const KATAKANA = [
        'ッキャ', 'ッキュ', 'ッキョ',
        'ッカ', 'ッキ', 'ック', 'ッケ', 'ッコ',
        'キャ', 'キュ', 'キョ',
        'カ', 'キ', 'ク', 'ケ', 'コ',
        
        'ッギャ', 'ッギュ', 'ッギョ',
        'ッガ', 'ッギ', 'ッグ', 'ッゲ', 'ッゴ',
        'ギャ', 'ギュ', 'ギョ',
        'ガ', 'ギ', 'グ', 'ゲ', 'ゴ',
        
        'ッチャ', 'ッチュ', 'ッチョ',
        'ッタ', 'ッチ', 'ッツ', 'ッテ', 'ット',
        'チャ', 'チュ', 'チョ',
        'タ', 'チ', 'ツ', 'テ', 'ト',

        'ッヂャ', 'ッヂュ', 'ッヂョ',
        'ッダ', 'ッヂ', 'ッヅ', 'ッデ', 'ッド',
        'ヂャ', 'ヂュ', 'ヂョ',
        'ダ', 'ヂ', 'ヅ', 'デ', 'ド',

        'ッシャ', 'ッシュ', 'ッショ',
        'ッサ', 'ッシ', 'ッス', 'ッセ', 'ッソ',
        'シャ', 'シュ', 'ショ',
        'サ', 'シ', 'ス', 'セ', 'ソ',
        
        'ッジャ', 'ッジュ', 'ッジョ',
        'ッザ', 'ッジ', 'ッズ', 'ッゼ', 'ッゾ',
        'ジャ', 'ジュ', 'ジョ',
        'ザ', 'ジ', 'ズ', 'ゼ', 'ゾ',
        
        'ッニャ', 'ッニュ', 'ッニョ',
        'ッナ', 'ッニ', 'ッヌ', 'ッネ', 'ッノ',
        'ニャ', 'ニュ', 'ニョ',
        'ナ', 'ニ', 'ヌ', 'ネ', 'ノ',
        
        'ッヒャ', 'ッヒュ', 'ッヒョ',
        'ッハ', 'ッヒ', 'ッフ', 'ッヘ', 'ッホ',
        'ヒャ', 'ヒュ', 'ヒョ',
        'ハ', 'ヒ', 'フ', 'ヘ', 'ホ',
        
        'ッビャ', 'ッビュ', 'ッビョ',
        'ッバ', 'ッビ', 'ッブ', 'ッベ', 'ッボ',
        'ビャ', 'ビュ', 'ビョ',
        'バ', 'ビ', 'ブ', 'ベ', 'ボ',
        
        'ッピャ', 'ッピュ', 'ッピョ',
        'ッパ', 'ッピ', 'ップ', 'ッペ', 'ッポ',
        'ピャ', 'ピュ', 'ピョ',
        'パ', 'ピ', 'プ', 'ペ', 'ポ',
        
        'ッミャ', 'ッミュ', 'ッミョ',
        'ッマ', 'ッミ', 'ッム', 'ッメ', 'ッモ',
        'ミャ', 'ミュ', 'ミョ',
        'マ', 'ミ', 'ム', 'メ', 'モ',
        
        'ッリャ', 'ッリュ', 'ッリョ',
        'ッラ', 'ッリ', 'ッル', 'ッレ', 'ッロ',
        'リャ', 'リュ', 'リョ',
        'ラ', 'リ', 'ル', 'レ', 'ロ',

        'ッヤ', 'ッユ', 'ッヨ',
        'ヤ', 'ユ', 'ヨ',
        
        'ワ', 'ヲ', 'ン',
        'ア', 'イ', 'ウ', 'エ', 'オ',
        '', '、'
    ];

    private const KATAKANA_LONG = [
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
        $katakana = str_replace(
            self::KATAKANA_LONG, self::KATAKANA_LONG_FIXED, $katakana
        );

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

    private static function convert(string $string, array $to): string
    {
        $from = self::detect($string);
        return str_replace($from, $to, $string);
    }

    private static function detect(string $string): array
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
