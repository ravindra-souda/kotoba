<?php

declare(strict_types=1);

namespace App\Document\Trait\Script;

trait ScriptTrait
{
    private const ROMAJI = [
        'kka', 'kki', 'kku', 'kke', 'kko',
        'ka', 'ki', 'ku', 'ke', 'ko',
        'kkya', 'kkyu', 'kkyo',
        'kya', 'kyu', 'kyo',
        'gga', 'ggi', 'ggu', 'gge', 'ggo',
        'ga', 'gi', 'gu', 'ge', 'go',
        'ggya', 'ggyu', 'ggyo',
        'gya', 'gyu', 'gyo',
        'tta', 'cchi', 'ttsu', 'tte', 'tto',
        'ta', 'chi', 'tsu', 'te', 'to',
        'ssa', 'sshi', 'ssu', 'sse', 'sso',
        'sa', 'shi', 'su', 'se', 'so',
        'ssha', 'sshu', 'ssho',
        'sha', 'shu', 'sho',
        'zza', 'jji', 'zzu', 'zze', 'zzo',
        'za', 'ji', 'zu', 'ze', 'zo',
        'jjya', 'jjyu', 'jjyo',
        'jya', 'jyu', 'jyo',
        'ccha', 'cchu', 'ccho',
        'cha', 'chu', 'cho',
        'dda', 'ddi', 'ddu', 'dde', 'ddo',
        'da', 'di', 'du', 'de', 'do',
        'ddya', 'ddyu', 'ddyo',
        'dya', 'dyu', 'dyo',
        'nna', 'nni', 'nnu', 'nne', 'nno',
        'na', 'ni', 'nu', 'ne', 'no',
        'nnya', 'nnyu', 'nnyo',
        'nya', 'nyu', 'nyo',
        'hha', 'hhi', 'ffu', 'hhe', 'hho',
        'ha', 'hi', 'fu', 'he', 'ho',
        'hhya', 'hhyu', 'hhyo',
        'hya', 'hyu', 'hyo',
        'bba', 'bbi', 'bbu', 'bbe', 'bbo',
        'ba', 'bi', 'bu', 'be', 'bo',
        'bbya', 'bbyu', 'bbyo',
        'bya', 'byu', 'byo',
        'ppa', 'ppi', 'ppu', 'ppe', 'ppo',
        'pa', 'pi', 'pu', 'pe', 'po',
        'ppya', 'ppyu', 'ppyo',
        'pya', 'pyu', 'pyo',
        'mma', 'mmi', 'mmu', 'mme', 'mmo',
        'ma', 'mi', 'mu', 'me', 'mo',
        'mmya', 'mmyu', 'mmyo',
        'mya', 'myu', 'myo',
        'yya', 'yyu', 'yyo',
        'ya', 'yu', 'yo',
        'rra', 'rri', 'rru', 'rre', 'rro',
        'ra', 'ri', 'ru', 're', 'ro',
        'rrya', 'rryu', 'rryo',
        'rya', 'ryu', 'ryo',
        'wa', 'wo', 'n',
        'a', 'i', 'u', 'e', 'o',
        ' ', ',',
    ];

    private const HIRAGANA = [
        'っか', 'っき', 'っく', 'っけ', 'っこ',
        'か', 'き', 'く', 'け', 'こ',
        'っきゃ', 'っきゅ', 'っきょ',
        'きゃ', 'きゅ', 'きょ',
        'っが', 'っぎ', 'っぐ', 'っげ', 'っご',
        'が', 'ぎ', 'ぐ', 'げ', 'ご',
        'っぎゃ', 'っぎゅ', 'っぎょ',
        'ぎゃ', 'ぎゅ', 'ぎょ',
        'った', 'っち', 'っつ', 'って', 'っと',
        'た', 'ち', 'つ', 'て', 'と',
        'っさ', 'っし', 'っす', 'っせ', 'っそ',
        'さ', 'し', 'す', 'せ', 'そ',
        'っしゃ', 'っしゅ', 'っしょ',
        'しゃ', 'しゅ', 'しょ',
        'っざ', 'っじ', 'っず', 'っぜ', 'っぞ',
        'ざ', 'じ', 'ず', 'ぜ', 'ぞ',
        'っじゃ', 'っじゅ', 'っじょ',
        'じゃ', 'じゅ', 'じょ',
        'っちゃ', 'っちゅ', 'っちょ',
        'ちゃ', 'ちゅ', 'ちょ',
        'っだ', 'っぢ', 'っづ', 'っで', 'っど',
        'だ', 'ぢ', 'づ', 'で', 'ど',
        'っぢゃ', 'っぢゅ', 'っぢょ',
        'ぢゃ', 'ぢゅ', 'ぢょ',
        'っな', 'っに', 'っぬ', 'っね', 'っの',
        'な', 'に', 'ぬ', 'ね', 'の',
        'っにゃ', 'っにゅ', 'っにょ',
        'にゃ', 'にゅ', 'にょ',
        'っは', 'っひ', 'っふ', 'っへ', 'っほ',
        'は', 'ひ', 'ふ', 'へ', 'ほ',
        'っひゃ', 'っひゅ', 'っひょ',
        'ひゃ', 'ひゅ', 'ひょ',
        'っば', 'っび', 'っぶ', 'っべ', 'っぼ',
        'ば', 'び', 'ぶ', 'べ', 'ぼ',
        'っびゃ', 'っびゅ', 'っびょ',
        'びゃ', 'びゅ', 'びょ',
        'っぱ', 'っぴ', 'っぷ', 'っぺ', 'っぽ',
        'ぱ', 'ぴ', 'ぷ', 'ぺ', 'ぽ',
        'っぴゃ', 'っぴゅ', 'っぴょ',
        'ぴゃ', 'ぴゅ', 'ぴょ',
        'っま', 'っみ', 'っむ', 'っめ', 'っも',
        'ま', 'み', 'む', 'め', 'も',
        'っみゃ', 'っみゅ', 'っみょ',
        'みゃ', 'みゅ', 'みょ',
        'っや', 'っゆ', 'っよ',
        'や', 'ゆ', 'よ',
        'っら', 'っり', 'っる', 'っれ', 'っろ',
        'ら', 'り', 'る', 'れ', 'ろ',
        'っりゃ', 'っりゅ', 'っりょ',
        'りゃ', 'りゅ', 'りょ',
        'わ', 'を', 'ん',
        'あ', 'い', 'う', 'え', 'お',
        '', '、'
    ];

    private const KATAKANA = [
        'ッカ', 'ッキ', 'ック', 'ッケ', 'ッコ',
        'カ', 'キ', 'ク', 'ケ', 'コ',
        'ッキャ', 'ッキュ', 'ッキョ',
        'キャ', 'キュ', 'キョ',
        'ッガ', 'ッギ', 'ッグ', 'ッゲ', 'ッゴ',
        'ガ', 'ギ', 'グ', 'ゲ', 'ゴ',
        'ッギャ', 'ッギュ', 'ッギョ',
        'ギャ', 'ギュ', 'ギョ',
        'ッタ', 'ッチ', 'ッツ', 'ッテ', 'ット',
        'タ', 'チ', 'ツ', 'テ', 'ト',
        'ッサ', 'ッシ', 'ッス', 'ッセ', 'ッソ',
        'サ', 'シ', 'ス', 'セ', 'ソ',
        'ッシャ', 'ッシュ', 'ッショ',
        'シャ', 'シュ', 'ショ',
        'ッザ', 'ッジ', 'ッズ', 'ッゼ', 'ッゾ',
        'ザ', 'ジ', 'ズ', 'ゼ', 'ゾ',
        'ッジャ', 'ッジュ', 'ッジョ',
        'ジャ', 'ジュ', 'ジョ',
        'ッチャ', 'ッチュ', 'ッチョ',
        'チャ', 'チュ', 'チョ',
        'ッダ', 'ッジ', 'ッヅ', 'ッデ', 'ッド',
        'ダ', 'ジ', 'ヅ', 'デ', 'ド',
        'ッジャ', 'ッジュ', 'ッジョ',
        'ジャ', 'ジュ', 'ジョ',
        'ッナ', 'ッニ', 'ッヌ', 'ッネ', 'ッノ',
        'ナ', 'ニ', 'ヌ', 'ネ', 'ノ',
        'ッニャ', 'ッニュ', 'ッニョ',
        'ニャ', 'ニュ', 'ニョ',
        'ッハ', 'ッヒ', 'ッフ', 'ッヘ', 'ッホ',
        'ハ', 'ヒ', 'フ', 'ヘ', 'ホ',
        'ッヒャ', 'ッヒュ', 'ッヒョ',
        'ヒャ', 'ヒュ', 'ヒョ',
        'ッバ', 'ッビ', 'ッブ', 'ッベ', 'ッボ',
        'バ', 'ビ', 'ブ', 'ベ', 'ボ',
        'ッビャ', 'ッビュ', 'ッビョ',
        'ビャ', 'ビュ', 'ビョ',
        'ッパ', 'ッピ', 'ップ', 'ッペ', 'ッポ',
        'パ', 'ピ', 'プ', 'ペ', 'ポ',
        'ッピャ', 'ッピュ', 'ッピョ',
        'ピャ', 'ピュ', 'ピョ',
        'ッマ', 'ッミ', 'ッム', 'ッメ', 'ッモ',
        'マ', 'ミ', 'ム', 'メ', 'モ',
        'ッミャ', 'ッミュ', 'ッミョ',
        'ミャ', 'ミュ', 'ミョ',
        'ッヤ', 'ッユ', 'ッヨ',
        'ヤ', 'ユ', 'ヨ',
        'ッラ', 'ッリ', 'ッル', 'ッレ', 'ッロ',
        'ラ', 'リ', 'ル', 'レ', 'ロ',
        'ッリャ', 'ッリュ', 'ッリョ',
        'リャ', 'リュ', 'リョ',
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
}
