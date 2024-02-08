<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\SaveProcessor;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    public const VALID_GODAN_ENDINGS = [
        'う', 'く', 'ぐ', 'す', 'つ', 'ぬ', 'ぶ', 'む', 'る',
    ];

    public const IRREGULAR_VERBS = ['する', '為る', 'くる', '来る'];

    public const VALIDATION_ERR_ICHIDAN = 'Ichidan verbs must end with a る';

    public const VALIDATION_ERR_GODAN =
        'Godan verbs must end with one of these: {{ validGodanEndings }}';

    public const VALIDATION_ERR_IRREGULAR =
        'Irregular verbs can only be one of these: {{ irregularVerbs }}';

    public const VALIDATION_ERR_IS_IRREGULAR =
        '{{ irregularVerbs }} are irregular verbs';

    public const VALIDATION_ERR_DICTIONARY =
        "Inflections array must have a filled 'dictionary' value";

    private const VALIDATION_ERR_INFLECTIONS = [
        1 => self::VALIDATION_ERR_ICHIDAN,
        2 => self::VALIDATION_ERR_GODAN,
        3 => self::VALIDATION_ERR_IRREGULAR,
        4 => self::VALIDATION_ERR_IS_IRREGULAR,
        5 => self::VALIDATION_ERR_DICTIONARY,
    ];

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

    public static function isValidKatakana(?string $string): bool
    {
        if (null === $string || '' === $string) {
            return true;
        }

        // must start with katakana and can end with る
        return 1 === preg_match('/^\p{Katakana}+る?$/um', $string)
            // half-width katakana are not allowed
            && 1 !== preg_match('/[\x{FF65}-\x{FF9F}]/um', $string);
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
    public function hasValidGroup(
        array|string|null $inflections
    ): int {
        if (!$this->hasValidInflections($inflections)) {
            return 5;
        }

        $dict = $inflections['dictionary'];

        if (self::ICHIDAN === $this->group && !str_ends_with($dict, 'る')) {
            return 1;
        }

        if (self::GODAN === $this->group
            && !in_array(mb_substr($dict, -1), self::VALID_GODAN_ENDINGS)) {
            return 2;
        }

        if (self::IRREGULAR === $this->group
            && !in_array($dict, self::IRREGULAR_VERBS)) {
            return 3;
        }

        if (in_array($dict, self::IRREGULAR_VERBS)
            && self::IRREGULAR !== $this->group) {
            return 4;
        }

        return 0;
    }

    /**
     * @param array<string,array<mixed>|string> $inflections
     */
    public function hasValidInflections(
        array|string|null $inflections
    ): bool {
        if (null === $inflections || '' === $inflections) {
            return false;
        }

        if (empty($inflections['dictionary'])) {
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
        return $this
            ->setLowerAndTrimmedOrNull('inflections', $inflections, false)
        ;
    }

    public function setReviewed(bool $reviewed): Verb
    {
        $this->reviewed = $reviewed;

        return $this;
    }

    #[Assert\Callback]
    public function validateGroup(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        $errCode = $this->hasValidGroup($this->inflections);
        if (0 === $errCode) {
            return;
        }

        $errMessages = [
            1 => self::VALIDATION_ERR_INFLECTIONS[1],
            2 => self::formatMsg(
                self::VALIDATION_ERR_INFLECTIONS[2],
                self::VALID_GODAN_ENDINGS
            ),
            3 => self::formatMsg(
                self::VALIDATION_ERR_INFLECTIONS[3],
                self::IRREGULAR_VERBS
            ),
            4 => self::formatMsg(
                self::VALIDATION_ERR_INFLECTIONS[4],
                self::IRREGULAR_VERBS
            ),
            5 => self::VALIDATION_ERR_INFLECTIONS[5],
        ];

        $context
            ->buildViolation($errMessages[$errCode])
            ->atPath('group')
            ->addViolation()
        ;
    }

    #[Assert\Callback]
    public function validateInflections(
        ExecutionContextInterface $context,
        mixed $payload
    ): void {
        if ($this->hasValidInflections($this->inflections)) {
            return;
        }

        $context
            ->buildViolation(self::VALIDATION_ERR_DICTIONARY)
            ->atPath('inflections')
            ->addViolation()
        ;
    }

    public function conjugate(): Verb
    {
        if (0 !== $this->hasValidGroup($this->inflections)) {
            throw new \Exception(
                'Set inflections are not valid or group mismatch'
            );
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

    private function fillRomaji(): self
    {
        $katakanaString = str_replace('る', 'ル', $this->katakana ?? '');
        $this->romaji ??= $this->toRomaji($this->hiragana ?? $katakanaString);

        return $this;
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
