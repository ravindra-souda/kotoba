<?php

declare(strict_types=1);

use App\Document\Noun;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
class ScriptTest extends TestCase
{
    /**
     * @return array<string,array<null|false|string>>
     */
    public static function toHiraganaProvider(): array
    {
        $tests = [
            'empty' => ['', null],
            'null' => [null, null],
            ['a', 'あ'],
            ['ア', 'あ'],
            ['あ', 'あ'],
            ['chi', 'ち'],
            ['shi', 'し'],
            ['sho', 'しょ'],
            ['シャ', 'しゃ'],
            ['cha', 'ちゃ'],
            ['チョ', 'ちょ'],
            ['ju', 'じゅ'],
            ['ジョ', 'じょ'],
            ['djo', 'ぢょ'],
            ['ヂュ', 'ぢゅ'],
            ['chō', 'ちょお'],
            ['chou', 'ちょう'],
            ['シャー', 'しゃあ'],
            ['byou', 'びょう'],
            ['ピャ', 'ぴゃ'],
            ['oishii', 'おいしい'],
            ['sensei', 'せんせい'],
            ['nihon', 'にほん'],
            ['gakkou', 'がっこう'],
            ['ohayou gozaimasu', 'おはようございます'],
            ['matcha', 'まっちゃ'],
            ['hi, bi, ka', 'ひ、び、か'],
            ['スーパーマリオブラザーズ', 'すうぱあまりおぶらざあず'],
            ['コーヒー', 'こおひい'],
            ['ケチャップ', 'けちゃっぷ'],
            ['ハッピー', 'はっぴい'],
            ['スーパーマーケット', 'すうぱあまあけっと'],
            ['コンビニ', 'こんびに'],
            ['ニュージーランド', 'にゅうじいらんど'],
            ['字', false],
            ['食べる', false],
            'integer' => ['1', false],
            ['oisii', false],
            ['ohayou gozaimas', false],
            ['maccha', false],
        ];

        return self::setProviderKeys($tests);
    }

    /**
     * @param ?string $string
     */
    #[DataProvider('toHiraganaProvider')]
    public function testToHiragana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $this->assertEquals($expected, Noun::toHiragana($string));
    }

    /**
     * @return array<string,array<null|false|string>>
     */
    public static function toKatakanaProvider(): array
    {
        $tests = [
            'empty' => ['', null],
            'null' => [null, null],
            ['a', 'ア'],
            ['ア', 'ア'],
            ['あ', 'ア'],
            ['chi', 'チ'],
            ['shi', 'シ'],
            ['shu', 'シュ'],
            ['しょ', 'ショ'],
            ['cha', 'チャ'],
            ['ちゅ', 'チュ'],
            ['jo', 'ジョ'],
            ['じゃ', 'ジャ'],
            ['dju', 'ヂュ'],
            ['ぢゃ', 'ヂャ'],
            ['djā', 'ヂャー'],
            ['じょお', 'ジョー'],
            ['mya', 'ミャ'],
            ['りょ', 'リョ'],
            ['pan', 'パン'],
            ['yuniiku', 'ユニーク'],
            ['merii kurisumasu', 'メリークリスマス'],
            ['sūpā mario burazāzu', 'スーパーマリオブラザーズ'],
            ['hotchikisu', 'ホッチキス'],
            ['nichi, jitsu', 'ニチ、ジツ'],
            ['字', false],
            ['食べる', false],
            'integer' => ['1', false],
            ['si', false],
            ['merii kurisumas', false],
            ['hocchikisu', false],
        ];

        return self::setProviderKeys($tests);
    }

    /**
     * @dataProvider toKatakanaProvider
     *
     * @param ?string $string
     */
    #[DataProvider('toKatakanaProvider')]
    public function testToKatakana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $this->assertEquals($expected, Noun::toKatakana($string));
    }

    /**
     * @return array<string,array<null|false|string>>
     */
    public static function toRomajiProvider(): array
    {
        $tests = [
            'empty' => ['', null],
            'null' => [null, null],
            ['あ', 'a'],
            ['ア', 'a'],
            ['a', 'a'],
            ['しゃ', 'sha'],
            ['シュ', 'shu'],
            ['ちゅ', 'chu'],
            ['チョ', 'cho'],
            ['じゃ', 'ja'],
            ['ジュ', 'ju'],
            ['ぢゃ', 'dja'],
            ['ヂョ', 'djo'],
            ['しょお', 'shō'],
            ['チュー', 'chū'],
            ['にょ', 'nyo'],
            ['ギュ', 'gyu'],
            ['だいがくせい', 'daigakusei'],
            ['がっこうきゅうしょく', 'gakkoukyūshoku'],
            ['スーパーマリオブラザーズ', 'sūpāmarioburazāzu'],
            ['コーヒー', 'kōhii'],
            ['ケチャップ', 'kechappu'],
            ['ハッピー', 'happii'],
            ['スーパーマーケット', 'sūpāmāketto'],
            ['コンビニ', 'konbini'],
            ['ニュージーランド', 'nyūjiirando'],
            ['まっちゃ', 'matcha'],
            ['ホッチキス', 'hotchikisu'],
            ['ひ、び、か', 'hi,bi,ka'],
            ['ニチ、ジツ', 'nichi,jitsu'],
            ['merii kurisumas', 'merii kurisumas'],
            ['字', false],
            ['食べる', false],
            'integer' => ['1', false],
        ];

        return self::setProviderKeys($tests);
    }

    /**
     * @param ?string $string
     */
    #[DataProvider('toRomajiProvider')]
    public function testToRomaji(
        ?string $string,
        string|null|bool $expected,
    ): void {
        $this->assertEquals($expected, Noun::toRomaji($string));
    }

    /**
     * @return array<array<null|false|string>>
     */
    public static function detectProvider(): array
    {
        $tests = [
            ['あ', Noun::HIRAGANA],
            ['しゃ', Noun::HIRAGANA],
            ['ひ、び、か', Noun::HIRAGANA],
            ['ゐ', Noun::HIRAGANA],
            ['ア', Noun::KATAKANA],
            ['シュ', Noun::KATAKANA],
            ['ヒ、ビ、カ', Noun::KATAKANA],
            ['ヰ', Noun::KATAKANA],
            ['a', Noun::ROMAJI],
            ['ā', Noun::ROMAJI],
            'empty' => ['', null],
            'integer' => ['1', null],
            ['字', null],
            ['食べる', null],
            ['ちュ', null],
            ['チょ', null],
            ['あa', null],
            ['アa', null],
            ['あア', null],
        ];

        return self::setProviderKeys($tests);
    }

    #[DataProvider('detectProvider')]
    public function testDetect(
        string $string,
        string|null $expected,
    ): void {
        $this->assertEquals($expected, Noun::detect($string));
    }

    /**
     * @param array<int|string,array<null|false|string>> $tests
     *
     * @return array<string,array<null|false|string>>
     */
    private static function setProviderKeys(array $tests): array
    {
        $provider = [];
        foreach ($tests as $key => $test) {
            if (is_int($key)) {
                $key = str_replace(' ', '_', (string) $test[0]);
            }
            $provider[$key] = $test;
        }

        return $provider;
    }
}
