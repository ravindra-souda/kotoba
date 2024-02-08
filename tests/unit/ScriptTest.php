<?php

declare(strict_types=1);

use App\Document\Trait\Script\ScriptTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ScriptTest extends TestCase
{
    /**
     * @return array<array<null|false|string>>
     */
    public function toHiraganaProvider(): array
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

        return $this->setProviderKeys($tests);
    }

    /**
     * @dataProvider toHiraganaProvider
     *
     * @param ?string $string
     */
    public function testToHiragana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toHiragana($string), $expected);
    }

    /**
     * @return array<array<null|false|string>>
     */
    public function toKatakanaProvider(): array
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

        return $this->setProviderKeys($tests);
    }

    /**
     * @dataProvider toKatakanaProvider
     *
     * @param ?string $string
     */
    public function testToKatakana(
        ?string $string,
        string|null|bool $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toKatakana($string), $expected);
    }

    /**
     * @return array<array<null|false|string>>
     */
    public function toRomajiProvider(): array
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

        return $this->setProviderKeys($tests);
    }

    /**
     * @dataProvider toRomajiProvider
     *
     * @param ?string $string
     */
    public function testToRomaji(
        ?string $string,
        string|null|bool $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(ScriptTrait::class);
        $this->assertEquals($mock->toRomaji($string), $expected);
    }

    /**
     * @param array<int|string,array<null|bool|string>> $tests
     *
     * @return array<string,array<null|bool|string>>
     */
    private function setProviderKeys(array $tests): array
    {
        $provider = [];
        foreach ($tests as $key => $test) {
            if (is_int($key)) {
                $key = str_replace(' ', '_', $test[0]);
            }
            $provider[$key] = $test;
        }

        return $provider;
    }
}
