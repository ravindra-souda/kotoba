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

    private const ICHIDAN_SUFFIXES = [
        'non-past' => [
            'informal' => [
                'affirmative' => '',
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

    private const GODAN_SUFFIXES = [
        'non-past' => [
            'informal' => [
                'affirmative' => '',
                'negative' => '{a}ない',
            ],
            'polite' => [
                'affirmative' => '{i}ます',
                'negative' => '{i}ません',
            ],
        ],
        'past' => [
            'informal' => [
                'affirmative' => '{i-past-te}た', 
                'negative' => '{a}なかった',
            ],
            'polite' => [
                'affirmative' => '{i}ました',
                'negative' => '{i}ませんでした',
            ],
        ],
        'te' => [
            'affirmative' => '{i-past-te}て',
            'negative' => '{a}なくて',
        ],
        'potential' => [
            'affirmative' => '{e}る',
            'negative' => '{e}ない',
        ],
        'passive' => [
            'affirmative' => '{a}れる',
            'negative' => '{a}れない',
        ],
        'causative' => [
            'affirmative' => '{a}せる',
            'negative' => '{a}せない',
            'passive' => [
                'affirmative' => '{a}せられる',
                'negative' => '{a}せられない',
            ]
        ],
        'imperative' => [
            'affirmative' => '{e}',
            'negative' => '{u}な',
        ],
    ];

    private const OKURIGANA = [
        'う' => ['わ', 'い', 'う', 'え', 'お', 'っ'],
        'く' => ['か', 'き', 'く', 'け', 'こ', 'い'],
        'ぐ' => ['が', 'ぎ', 'ぐ', 'げ', 'ご', 'い'],
        'す' => ['さ', 'し', 'す', 'せ', 'そ', 'し'],
        'つ' => ['た', 'ち', 'つ', 'て', 'と', 'っ'],
        'ぬ' => ['な', 'に', 'ぬ', 'ね', 'の', 'ん'],
        'ぶ' => ['ば', 'び', 'ぶ', 'べ', 'ぼ', 'ん'],
        'む' => ['ま', 'み', 'む', 'め', 'も', 'ん'],
        'る' => ['ら', 'り', 'る', 'れ', 'ろ', 'っ']
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
            'う', 'く', 'ぐ', 'す', 'つ', 'ぬ', 'ぶ', 'む', 'る'
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

        if ($this->group === self::GODAN) {
            return $this->conjugateGodan();
        }

        if ($this->group === self::IRREGULAR) {
            return $this;
        }
    }

    private function conjugateIchidan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($inflections['dictionary'], 0, -1);

        $autoConjugations = self::ICHIDAN_SUFFIXES;
        array_walk_recursive(
            $autoConjugations,
            fn(&$v, $k) => $v = $root.$v,
        );

        return $this->fillEmptyInflections($autoConjugations);
    }

    private function conjugateGodan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($inflections['dictionary'], 0, -1);
        $lastOkurigana = mb_substr($inflections['dictionary'], -1);

        $autoConjugations = self::GODAN_SUFFIXES;
        $vowels = ['{a}', '{i}', '{u}', '{e}', '{o}', '{i-past-te}'];
        $okurigana = self::OKURIGANA[$lastOkurigana];

        array_walk_recursive(
            $autoConjugations,
            fn(&$v, $k) => $v = str_replace($vowels, $okurigana, $root.$v),
        );

        return $this->fillEmptyInflections($autoConjugations);
    }

    private function fillEmptyInflections(array $autoConjugations): Verb
    {
        $inflections = $this->getInflections();

        foreach ($autoConjugations as $tense => $autoConjugation) {
            if (empty($inflections[$tense])) {
                $inflections[$tense] = $autoConjugation;
            }
        }

        $inflections['non-past']['informal']['affirmative'] = 
            $inflections['dictionary'];

        return $this
            ->setInflections($inflections)
            ->setReviewed(false);
    }
}
