<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    operations: [
        new Post(uriTemplate: '/cards/kana'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\KanaRepository')]
class Kana extends Card
{
    use Trait\HiraganaTrait;
    use Trait\KatakanaTrait;
    use Trait\RomajiTrait;

    public const HIRAGANA_MAXLENGTH = 2;

    public const KATAKANA_MAXLENGTH = 2;

    public const ROMAJI_MAXLENGTH = 4;

    public const VALIDATION_ERR_KANA_HIRAGANA =
        'must be exactly one mora long and written using only hiragana';

    public const VALIDATION_ERR_KANA_KATAKANA =
        'must be exactly one mora long and written using only katakana';

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this->fillRomaji();
    }

    /**
     * @return array<string,array<string,array<string>>>
     */
    public static function getFields(): array
    {
        return [
            'string' => [
                'trim' => ['hiragana', 'katakana'],
                'lower+trim' => ['romaji'],
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return $this->romaji;
    }

    public static function isValidHiragana(?string $string): bool
    {
        if (null === $string || '' === $string) {
            return true;
        }

        $excludedCharsFromHiraganaSet =
            'ぁぃぅぇぉっゃゅょゎゐゑゔゕゖ\x{3099}-\x{309F}';

        // any regular sized hiragana preceeded eventually by a chiisai tsu
        $regularSizedHiraganaRegExp =
            '/^っ?(?!['.$excludedCharsFromHiraganaSet.'])\p{Hiragana}$/um';

        // allowed hiragana glides
        $glidesRegExp = '/^[きしちにひみりぎじぢびぴ][ゃゅょ]$/um';

        return 1 === preg_match($regularSizedHiraganaRegExp, $string)
            || 1 === preg_match($glidesRegExp, $string);
    }

    public static function isValidKatakana(?string $string): bool
    {
        if (null === $string || '' === $string) {
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

        return 1 === preg_match($regularSizedKatakanaRegExp, $string)
            || 1 === preg_match($glidesRegExp, $string)
            || 1 === preg_match($specialGlidesRegExp, $string);
    }

    #[Assert\Callback]
    public function validateHiragana(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidHiragana($this->hiragana)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_KANA_HIRAGANA)
            ->atPath('hiragana')
            ->addViolation()
        ;
    }

    #[Assert\Callback]
    public function validateKatakana(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidKatakana($this->katakana)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_KANA_KATAKANA)
            ->atPath('katakana')
            ->addViolation()
        ;
    }
}
