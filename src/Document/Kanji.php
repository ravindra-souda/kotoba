<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\FetchKanjiByCode;
use App\Filter\YomiFilter;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiFilter(
    SearchFilter::class,
    properties: [
        'kanji' => 'exact',
    ],
)]
#[ApiResource(
    routePrefix: '/cards',
    operations: [
        new Post(),
        new Delete(),
        new Put(
            controller: FetchKanjiByCode::class,
            uriTemplate: '/kanji/{code}',
            /* bypassing faulty internal document fetching with our custom
               controller */
            read: false
        ),
        new Get(),
        new GetCollection(),
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

    public const VALIDATION_ERR_KANJI =
        'must be written using exactly one kanji';

    public const VALIDATION_ERR_ONYOMI =
        'must be written using only lowercase roman or katakana characters, '.
        'will be converted to katakana by the API';

    public const VALIDATION_ERR_KUNYOMI =
        'must be written using only lowercase roman or hiragana characters, '.
        'will be converted to hiragana by the API';

    public const VALIDATION_ERR_NO_KUNYOMI_NOR_ONYOMI =
        'either kunyomi or onyomi must be filled';

    /** Must be written using only kanji */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $kanji = '';

    /** Must be written using only lowercase roman or hiragana characters,
     *  will be converted to hiragana by the API.
     *
     * @var array<string>
     */
    #[Assert\All([
        new Assert\Regex(
            pattern: '/^\s*[a-zāūēō]+\s*$|^\s*\p{Hiragana}+\s*$/um',
            message: self::VALIDATION_ERR_KUNYOMI
        ),
    ])]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'collection')]
    #[ApiFilter(YomiFilter::class)]
    protected ?array $kunyomi = null;

    /** Must be written using only lowercase roman or katakana characters,
     *  will be converted to katakana by the API.
     *
     * @var array<string>
     */
    #[Assert\All([
        new Assert\Regex(
            pattern: '/^\s*[a-zāūēō]+\s*$|^\s*\p{Katakana}+\s*$/um',
            message: self::VALIDATION_ERR_ONYOMI
        ),
    ])]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'collection')]
    #[ApiFilter(YomiFilter::class)]
    protected ?array $onyomi = null;

    public function getKanji(): string
    {
        return $this->kanji;
    }

    /**
     * @return ?array<string>
     */
    public function getKunyomi(): ?array
    {
        return $this->kunyomi;
    }

    /**
     * @return ?array<string>
     */
    public function getOnyomi(): ?array
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
        return $this->setLowerAndTrimmedOrNull('kanji', $kanji);
    }

    /**
     * @param ?array<string> $kunyomi
     */
    public function setKunyomi(?array $kunyomi): Kanji
    {
        return $this->setLowerAndTrimmedOrNull('kunyomi', $kunyomi);
    }

    /**
     * @param ?array<string> $onyomi
     */
    public function setOnyomi(?array $onyomi): Kanji
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
        return $this->meaning['en'][0][0];
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
        $this->kunyomi = array_map([$this, 'toHiragana'], $this->kunyomi ?? []);

        return $this;
    }

    private function fillOnyomi(): Kanji
    {
        $this->onyomi = array_map(
            fn ($v) => $this->toKatakana($v, false),
            $this->onyomi ?? []
        );

        return $this;
    }
}
