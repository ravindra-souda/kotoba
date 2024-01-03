<?php

declare(strict_types=1);

namespace App\Document;

final class Kana extends Card
{
    public const VALIDATION_ERR_KANA =
        'must be a mora long';

    public static function isValidHiragana(?string $string): bool
    {
        if ($string === null || $string === '') {
            return true;
        }

        $excludedCharsFromHiraganaSet = 
            'ぁぃぅぇぉっゃゅょゎゐゑゔゕゖ\x{3099}-\x{309F}';

        // any regular sized hiragana preceeded eventually by a chiisai tsu 
        $regularSizedHiraganaRegExp = 
            '/^っ?(?!['.$excludedCharsFromHiraganaSet.'])\p{Hiragana}$/um';

        // allowed hiragana glides
        $glidesRegExp = '/^[きしちにひみりぎじぢびぴ][ゃゅょ]$/um';
        
        return preg_match($regularSizedHiraganaRegExp, $string) === 1 
            || preg_match($glidesRegExp, $string) === 1;
    }

    public static function isValidKatakana(?string $string): bool
    {
        if ($string === null || $string === '') {
            return true;
        }

        $excludedCharsFromKatakanaSet = 
            'ァィゥェォッャュョヮヰヱヴヵヶヸヹ'.
            '\x{3099}-\x{309F}\x{30A0}\x{30FB}\x{30FD}-\x{30FF}'.
            // half-width katakana are also excluded
            '\x{FF65}-\x{FF9F}';

        // any regular sized katakana preceeded eventually by a chiisai tsu 
        $regularSizedKatakanaRegExp = 
            '/^ッ?(?!['.$excludedCharsFromKatakanaSet.'])\p{Katakana}ー?$/um';

        // allowed katakana glides
        $glidesRegExp = '/^[キシチニヒミリギジヂビピ][ャュョ]$/um';

        // special katakana glides
        $specialGlidesRegExp = 
            '/^[ヴフツ][ァィェォ]|ウ[ィェォ]|[シジチ]ェ|[トド]ゥ|[テデ]ィ$/um';
        
        return preg_match($regularSizedKatakanaRegExp, $string) === 1 
            || preg_match($glidesRegExp, $string) === 1
            || preg_match($specialGlidesRegExp, $string) === 1;
    }
}
