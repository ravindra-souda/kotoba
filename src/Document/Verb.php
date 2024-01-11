<?php

declare(strict_types=1);

namespace App\Document;

final class Verb extends Card
{
    use Trait\GroupTrait, 
        Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait;

    public const GODAN = 'godan';

    public const ICHIDAN = 'ichidan';

    public const IRREGULAR = 'irregular';

    public const ALLOWED_GROUPS = [
        self::GODAN,
        self::ICHIDAN,
        self::IRREGULAR,
    ];

    private const SUFFIXES = [
        'non-past' => [
            'informal' => [
                'affirmative' => 'る',
                'negative' => 'ない',
            ],
            'polite' => [
                'affirmative' => 'ます',
                'negative' => 'ません',
            ],
        ],
        'past' => [
            'informal' => [
                'affirmative' => 'た', 
                'negative' => 'なかった',
            ],
            'polite' => [
                'affirmative' => 'ました',
                'negative' => 'ませんでした',
            ],
        ],
        'te' => [
            'affirmative' => 'て',
            'negative' => 'なくて',
        ],
        'potential' => [
            'affirmative' => 'られる',
            'negative' => 'られない',
        ],
        'passive' => [
            'affirmative' => 'られる',
            'negative' => 'られない',
        ],
        'causative' => [
            'affirmative' => 'させる',
            'negative' => 'させない',
            'passive' => [
                'affirmative' => 'させられる',
                'negative' => 'させられない',
            ]
        ],
        'imperative' => [
            'affirmative' => 'ろ',
            'negative' => 'るな',
        ],
    ];

    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Assert\Type(
        type: 'array',
        message: Card::VALIDATION_ERR_NOT_AN_ARRAY,
    )]
    protected array $inflections;

    /** Reviewed by users after automatic conjugation */
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'bool')]
    protected bool $reviewed = false;

    public function getInflections(): array
    {
        return $this->inflections;
    }

    public function isReviewed(): bool
    {
        return $this->reviewed;
    }

    public function isValidInflections(
        array|string|null $inflections
    ): bool
    {
        if ($inflections === null || $inflections === '') {
            return false;
        }

        if (empty($inflections['dictionary'])) {
            return false;
        }

        $dict = $inflections['dictionary'];

        if ($this->group === self::ICHIDAN && !str_ends_with($dict, 'る')) {
            return false;
        }
        
        $validGodanEndings = [
            'う', 'く', 'す', 'つ', 'ぬ', 'ふ', 'む', 'ゆ', 'る', 
            'ぐ', 'ず', 'づ', 'ぶ', 'ぷ'
        ];

        if ($this->group === self::GODAN 
            && !in_array(mb_substr($dict, -1), $validGodanEndings)) {
            return false;
        }

        $irregularVerbs = ['する', 'くる', '来る'];

        if ($this->group !== self::IRREGULAR 
            && in_array($dict, $irregularVerbs)) {
            return false;
        }

        return true;
    }

    public static function isValidCompletedInflections(array $inflections): bool
    {
        foreach($inflections as $inflection) {
            if (empty($inflection)) {
                return false;
            }
        }
        
        return true;
    }

    public function setInflections(array $inflections): Verb
    {        
        $this->inflections = $this->trimArrayValues($inflections);

        return $this;
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

        if ($this->group === self::ICHIDAN) {
            return $this->conjugateIchidan();
        }

        if ($this->group === self::IRREGULAR) {
            return $this;
        }
    }

    private function conjugateIchidan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($this->inflections['dictionary'], 0, -1);

        $autoConjugations = self::SUFFIXES;
        array_walk_recursive(
            $autoConjugations,
            fn(&$v, $k) => $v = $root.$v,
        );

        foreach ($autoConjugations as $tense => $autoConjugation) {
            if (empty($inflections[$tense])) {
                $inflections[$tense] = $autoConjugation;
            }
        }

        return $this
            ->setInflections($inflections)
            ->setReviewed(false);
    }
}
