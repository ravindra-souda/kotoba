<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(uriTemplate: '/cards/verbs'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    processor: SaveProcessor::class,
)]
#[MongoDB\Document(repositoryClass: 'App\Repository\VerbRepository')]
class Verb extends Card
{
    use Trait\GroupTrait;
    use Trait\HiraganaTrait;
    use Trait\KanjiTrait;
    use Trait\KatakanaTrait;
    use Trait\MeaningTrait;
    use Trait\RomajiTrait;
    use Trait\Const\VerbTrait;

    public const GODAN = 'godan';

    public const ICHIDAN = 'ichidan';

    public const IRREGULAR = 'irregular';

    public const ALLOWED_GROUPS = [
        self::GODAN,
        self::ICHIDAN,
        self::IRREGULAR,
    ];

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    public const ROMAJI_MAXLENGTH = 50;

    /**
     * dictionary must be filled,
     * any value left empty will be completed by automatic conjugation.
     *
     * @var array<string,array<mixed>|string>
     */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Assert\Type(
        type: 'array',
        message: Card::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'hash')]
    protected array $inflections = [
        'dictionary' => '',
        'non-past' => [
            'informal' => [
                'affirmative' => '',
                'negative' => '',
            ],
            'polite' => [
                'affirmative' => '',
                'negative' => '',
            ],
        ],
        'past' => [
            'informal' => [
                'affirmative' => '',
                'negative' => '',
            ],
            'polite' => [
                'affirmative' => '',
                'negative' => '',
            ],
        ],
        'te' => [
            'affirmative' => '',
            'negative' => '',
        ],
        'potential' => [
            'affirmative' => '',
            'negative' => '',
        ],
        'passive' => [
            'affirmative' => '',
            'negative' => '',
        ],
        'causative' => [
            'affirmative' => '',
            'negative' => '',
            'passive' => [
                'affirmative' => '',
                'negative' => '',
            ],
        ],
        'imperative' => [
            'affirmative' => '',
            'negative' => '',
        ],
    ];

    /** Reviewed by users after automatic conjugation */
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'bool')]
    protected bool $reviewed = false;

    // called right before persist, see App\State\SaveProcessor
    public function finalizeTasks(): self
    {
        return $this->fillRomaji()->conjugate();
    }

    /**
     * @return array<string,array<mixed>|string>
     */
    public function getInflections(): array
    {
        return $this->inflections;
    }

    public function isReviewed(): bool
    {
        return $this->reviewed;
    }

    /**
     * @param array<string,array<mixed>|string> $inflections
     */
    public function isValidInflections(
        array|string|null $inflections
    ): bool {
        if (null === $inflections || '' === $inflections) {
            return false;
        }

        if (empty($inflections['dictionary'])) {
            return false;
        }

        $dict = $inflections['dictionary'];

        if (self::ICHIDAN === $this->group && !str_ends_with($dict, 'る')) {
            return false;
        }

        $validGodanEndings = [
            'う', 'く', 'ぐ', 'す', 'つ', 'ぬ', 'ぶ', 'む', 'る',
        ];

        if (self::GODAN === $this->group
            && !in_array(mb_substr($dict, -1), $validGodanEndings)) {
            return false;
        }

        if (self::IRREGULAR !== $this->group
            && in_array($dict, self::IRREGULAR_VERBS)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string,array<mixed>|string> $inflections
     */
    public static function isValidCompletedInflections(array $inflections): bool
    {
        foreach ($inflections as $inflection) {
            if (empty($inflection)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string,array<mixed>|string> $inflections
     */
    public function setInflections(array $inflections): Verb
    {
        return $this->setLowerAndTrimmedOrNull('inflections', $inflections);
    }

    public function setReviewed(bool $reviewed): Verb
    {
        $this->reviewed = $reviewed;

        return $this;
    }

    public function conjugate(): Verb
    {
        if (!$this->isValidInflections($this->inflections)) {
            throw new \Exception('Set inflections are not valid');
        }

        if (self::ICHIDAN === $this->group) {
            return $this->conjugateIchidan();
        }

        if (self::GODAN === $this->group) {
            return $this->conjugateGodan();
        }

        return $this->conjugateIrregular();
    }

    /**
     * @return array<string,array<string,array<string>>>
     */
    public static function getFields(): array
    {
        return [
            'string' => [
                'trim' => ['hiragana', 'katakana', 'kanji'],
                'lower+trim' => ['romaji'],
            ],
        ];
    }

    public function getSlugReference(): string
    {
        return $this->romaji;
    }

    private function conjugateIchidan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($inflections['dictionary'], 0, -1);

        $autoConjugations = self::ICHIDAN_INFLECTIONS;
        array_walk_recursive(
            $autoConjugations,
            fn (&$v) => $v = $root.$v,
        );

        return $this->fillEmptyInflections($autoConjugations);
    }

    private function conjugateIrregular(): Verb
    {
        $dict = $this->getInflections()['dictionary'];

        if (!in_array($dict, self::IRREGULAR_VERBS)) {
            return $this;
        }

        return $this
            ->setInflections(self::IRREGULAR_INFLECTIONS[$dict])
            ->setReviewed(true)
        ;
    }

    private function conjugateGodan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($inflections['dictionary'], 0, -1);
        $lastOkurigana = mb_substr($inflections['dictionary'], -1);

        $autoConjugations = self::GODAN_INFLECTIONS;
        $vowels = ['{a}', '{i}', '{u}', '{e}', '{o}', '{i-past}', '{i-te}'];
        $okurigana = self::OKURIGANA[$lastOkurigana];

        array_walk_recursive(
            $autoConjugations,
            fn (&$v) => $v = str_replace($vowels, $okurigana, $root.$v),
        );

        return $this->fillEmptyInflections($autoConjugations);
    }

    /**
     * @param array<string,array<mixed>|string> $autoConjugations
     */
    private function fillEmptyInflections(array $autoConjugations): Verb
    {
        $inflections = $this->getInflections();

        $inflections = array_replace_recursive($autoConjugations, $inflections);
        $inflections['non-past']['informal']['affirmative'] =
            $inflections['dictionary'];

        return $this
            ->fixIrregularities($inflections)
            ->setReviewed(false)
        ;
    }

    /**
     * @param array<string,array<mixed>|string> $inflections
     */
    private function fixIrregularities(array $inflections): Verb
    {
        switch (trim($inflections['dictionary'])) {
            case 'いく':
                $inflections['past']['informal']['affirmative'] = 'いった';
                $inflections['te']['affirmative'] = 'いって';

                break;

            case '行く':
                $inflections['past']['informal']['affirmative'] = '行った';
                $inflections['te']['affirmative'] = '行って';

                break;
        }

        return $this->setInflections($inflections);
    }
}
