<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    routePrefix: '/cards',
    operations: [
        new Post(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\KanjiRepository')]
class Kanji extends Card
{
    use Trait\MeaningTrait;
    use Trait\Script\ScriptTrait;

    public const KUNYOMI_MAXLENGTH = 100;

    public const ONYOMI_MAXLENGTH = 100;

    public const VALIDATION_ERR_KANJI =
        'must be written using exactly one kanji';

    public const VALIDATION_ERR_ONYOMI =
        'must be written using only lowercase roman characters, '.
        'will be converted to katakana by the API';

    public const VALIDATION_ERR_KUNYOMI =
        'must be written using only lowercase roman characters, '.
        'will be converted to hiragana by the API';

    public const VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI =
        'either kunyomi or onyomi must be filled';

    /** Must be written using only kanji */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $kanji;

    /** Must be written using only lowercase roman characters,
     *  will be converted to hiragana by the API */
    #[Assert\Regex(
        pattern: '/^[a-zāūēō ,]+$/',
        message: self::VALIDATION_ERR_KUNYOMI
    )]
    #[Assert\Length(
        max: self::KUNYOMI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $kunyomi = null;

    /** Must be written using only lowercase roman characters,
     *  will be converted to katakana by the API */
    #[Assert\Regex(
        pattern: '/^[a-zāūēō ,]+$/',
        message: self::VALIDATION_ERR_ONYOMI
    )]
    #[Assert\Length(
        max: self::ONYOMI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected ?string $onyomi = null;

    public function getKanji(): string
    {
        return $this->kanji;
    }

    public function getKunyomi(): ?string
    {
        return $this->kunyomi;
    }

    public function getOnyomi(): ?string
    {
        return $this->onyomi;
    }

    public static function isValidKanji(string $string): bool
    {
        // must be kanji only
        return 1 === preg_match('/^\p{Han}$/um', $string);
    }

    public function setKanji(string $kanji): Kanji
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function setKunyomi(?string $kunyomi): Kanji
    {
        return $this->setLowerAndTrimmedOrNull('kunyomi', $kunyomi);
    }

    public function setOnyomi(?string $onyomi): Kanji
    {
        return $this->setLowerAndTrimmedOrNull('onyomi', $onyomi);
    }

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this->fillKunyomi()->fillOnyomi();
    }

    /**
     * @return array<string,array<string,array<string>>>
     */
    public static function getFields(): array
    {
        return [
            'string' => [
                'trim' => ['kanji'],
                'lower+trim' => ['kunyomi', 'onyomi'],
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return explode(',', $this->meaning['en'][0], 2)[0];
    }

    public function hasKunyomiOrOnyomi(): bool
    {
        return null !== $this->kunyomi || null !== $this->onyomi;
    }

    #[Assert\Callback]
    public function validateKanji(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->isValidKanji($this->kanji)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_KANJI)
            ->atPath('kanji')
            ->addViolation()
        ;
    }

    #[Assert\Callback]
    public function validateHasKunyomiOrOnyomi(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->hasKunyomiOrOnyomi()) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI)
            ->atPath('kunyomi')
            ->addViolation()
        ;

        $context
            ->buildViolation(self::VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI)
            ->atPath('onyomi')
            ->addViolation()
        ;
    }

    private function fillKunyomi(): Kanji
    {
        $this->kunyomi = $this->toHiragana($this->kunyomi);

        return $this;
    }

    private function fillOnyomi(): Kanji
    {
        $this->onyomi = $this->toKatakana($this->onyomi, false);

        return $this;
    }
}
