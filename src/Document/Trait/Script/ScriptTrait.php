<?php

declare(strict_types=1);

namespace App\Document\Trait\Script;

trait ScriptTrait
{
    private const HIRAGANA = 'hiragana';

    private const KATAKANA = 'katakana';

    private const ROMAJI = 'romaji';

    private const MORA = [
        'hiragana' => [
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
        ],
        'katakana' => [
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
        ],
        'romaji' => [
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
        ]
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

    private const KATAKANA_LONG_TO = [
        'from' => [
            'ッカー', 'ッキー', 'ックー', 'ッケー', 'ッコー',
            'カー', 'キー', 'クー', 'ケー', 'コー',

            'ッガー', 'ッギー', 'ッグー', 'ッゲー', 'ッゴー',
            'ガー', 'ギー', 'グー', 'ゲー', 'ゴー',

            'ッサー', 'ッシー', 'ッスー', 'ッセー', 'ッソー',
            'サー', 'シー', 'スー', 'セー', 'ソー',

            'ッザー', 'ッジー', 'ッズー', 'ッゼー', 'ッゾー',
            'ザー', 'ジー', 'ズー', 'ゼー', 'ゾー',

            'ッター', 'ッチー', 'ッツー', 'ッテー', 'ットー',
            'ター', 'チー', 'ツー', 'テー', 'トー',

            'ッダー', 'ッジー', 'ッヅー', 'ッデー', 'ッドー',
            'ダー', 'ジー', 'ヅー', 'デー', 'ドー',

            'ッナー', 'ッニー', 'ッヌー', 'ッネー', 'ッノー',
            'ナー', 'ニー', 'ヌー', 'ネー', 'ノー',

            'ッハー', 'ッヒー', 'ッフー', 'ッヘー', 'ッホー',
            'ハー', 'ヒー', 'フー', 'ヘー', 'ホー',

            'ッバー', 'ッビー', 'ッブー', 'ッベー', 'ッボー',
            'バー', 'ビー', 'ブー', 'ベー', 'ボー',

            'ッパー', 'ッピー', 'ップー', 'ッペー', 'ッポー',
            'パー', 'ピー', 'プー', 'ペー', 'ポー',

            'ッマー', 'ッミー', 'ッムー', 'ッメー', 'ッモー',
            'マー', 'ミー', 'ムー', 'メー', 'モー',

            'ッヤー', 'ッユー', 'ッヨー',
            'ヤー', 'ユー', 'ヨー',
            'ャー', 'ュー', 'ョー',

            'ッラー', 'ッリー', 'ッルー', 'ッレー', 'ッロー',
            'ラー', 'リー', 'ルー', 'レー', 'ロー',

            'ッアー', 'ッイー', 'ッウー', 'ッエー', 'ッオー',
            'アー', 'イー', 'ウー', 'エー', 'オー',
        ],
        'hiragana' => [
            'っかあ', 'っきい', 'っくう', 'っけい', 'っこう',
            'かあ', 'きい', 'くう', 'けい', 'こう',

            'っがあ', 'っぎい', 'っぐう', 'っげい', 'っごう',
            'があ', 'ぎい', 'ぐう', 'げい', 'ごう',

            'っさあ', 'っしい', 'っすう', 'っせい', 'っそう',
            'さあ', 'しい', 'すう', 'せい', 'そう',

            'っざあ', 'っじい', 'っずう', 'っぜい', 'っぞう',
            'ざあ', 'じい', 'ずう', 'ぜい', 'ぞう',

            'ったあ', 'っちい', 'っつう', 'ってい', 'っとう',
            'たあ', 'ちい', 'つう', 'てい', 'とう',

            'っだあ', 'っぢい', 'っづう', 'っでい', 'っどう',
            'だあ', 'ぢい', 'づう', 'でい', 'どう',

            'っなあ', 'っにい', 'っぬう', 'っねい', 'っのう',
            'なあ', 'にい', 'ぬう', 'ねい', 'のう',

            'っはあ', 'っひい', 'っふう', 'っへい', 'っほう',
            'はあ', 'ひい', 'ふう', 'へい', 'ほう',

            'っばあ', 'っびい', 'っぶう', 'っべい', 'っぼう',
            'ばあ', 'びい', 'ぶう', 'べい', 'ぼう',

            'っぱあ', 'っぴい', 'っぷう', 'っぺい', 'っぽう',
            'ぱあ', 'ぴい', 'ぷう', 'ぺい', 'ぽう',

            'っまあ', 'っみい', 'っむう', 'っめい', 'っもう',
            'まあ', 'みい', 'むう', 'めい', 'もう',

            'っやあ', 'っゆう', 'っよう',
            'やあ', 'ゆう', 'よう',
            'ゃあ', 'ゅう', 'ょう',

            'っらあ', 'っりい', 'っるう', 'っれい', 'っろう',
            'らあ', 'りい', 'るう', 'れい', 'ろう',

            'っああ', 'っいい', 'っうう', 'っえい', 'っおう',
            'ああ', 'いい', 'うう', 'えい', 'おう',
        ],
        'romaji' => [
            'kkaa', 'kkii', 'kkuu', 'kkei', 'kkou',
            'kaa', 'kii', 'kuu', 'kei', 'kou',

            'ggaa', 'ggii', 'gguu', 'ggei', 'ggou',
            'gaa', 'gii', 'guu', 'gei', 'gou',

            'ssaa', 'sshii', 'ssuu', 'ssei', 'ssou',
            'saa', 'shii', 'suu', 'sei', 'sou',

            'zzaa', 'jjii', 'zzuu', 'zzei', 'zzou',
            'zaa', 'jii', 'zuu', 'zei', 'zou',

            'ttaa', 'cchii', 'ttsuu', 'ttei', 'ttou',
            'taa', 'chii', 'tsuu', 'tei', 'tou',

            'ddaa', 'ddii', 'dduu', 'ddei', 'ddou',
            'daa', 'dii', 'duu', 'dei', 'dou',

            'nnaa', 'nnii', 'nnuu', 'nnei', 'nnou',
            'naa', 'nii', 'nuu', 'nei', 'nou',

            'hhaa', 'hhii', 'hhuu', 'hhei', 'hhou',
            'haa', 'hii', 'huu', 'hei', 'hou',

            'bbaa', 'bbii', 'bbuu', 'bbei', 'bbou',
            'baa', 'bii', 'buu', 'bei', 'bou',

            'ppaa', 'ppii', 'ppuu', 'ppei', 'ppou',
            'paa', 'pii', 'puu', 'pei', 'pou',

            'mmaa', 'mmii', 'mmuu', 'mmei', 'mmou',
            'maa', 'mii', 'muu', 'mei', 'mou',

            'yyaa', 'yyuu', 'yyoo',
            'yaa', 'yuu', 'yoo',
            'yaa', 'yuu', 'yoo',

            'rraa', 'rrii', 'rruu', 'rrei', 'rrou',
            'raa', 'rii', 'ruu', 'rei', 'rou',

            'aa', 'ii', 'uu', 'ei', 'ou',
            'aa', 'ii', 'uu', 'ei', 'ou',
        ],
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

        var_dump($romaji);
        return self::isRomaji($romaji) ? $romaji : false;
    }

    private static function convert(string $string, string $to): string
    {
        $from = self::detect($string);

        if ($from === $to) {
            return $string;
        }

        if ($from === self::KATAKANA) {
            $string = str_replace(
                self::KATAKANA_LONG_TO['from'], 
                self::KATAKANA_LONG_TO[$to], 
                $string
            );
        }

        $string = str_replace(self::MORA[$from], self::MORA[$to], $string);

        if ($to === self::KATAKANA) {
            return str_replace(
                self::KATAKANA_LONG, self::KATAKANA_LONG_FIXED, $string
            );
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
        return preg_match('/^[a-z,]+$/um', $string) === 1;
    }
}
