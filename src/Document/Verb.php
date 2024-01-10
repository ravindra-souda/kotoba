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

    private const GODAN = 'godan';

    private const ICHIDAN = 'ichidan';

    private const IRREGULAR = 'irregular';

    public const ALLOWED_GROUPS = [
        GODAN,
        ICHIDAN,
        IRREGULAR,
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

    public function getInflections(): array
    {
        return $this->inflections;
    }

    public static function isValidInflections(
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
            && !in_array(substr($dict, -1), $validGodanEndings)) {
            return false;
        }
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

    public function setInflections(array $inflections): Card
    {        
        $this->inflections = trimArrayValues($inflections);

        return $this;
    }

    public function conjugate(): Verb
    {
        if (!$this->isValidInflections($this->inflections)) {
            return false;
        }

        if ($this->group === self::ICHIDAN) {
            return $this->conjugateIchidan();
        }
    }

    private function conjugateIchidan(): Verb
    {
        $inflections = $this->getInflections();
        $root = substr($this->inflections['dictionary'], 0, -1);
        $autoConjugations = array_map(fn($a) => $root.$a, $self::SUFFIXES);

        foreach ($autoConjugations as $tense => $autoConjugation) {
            if (empty($inflections[$tense])) {
                $inflections[$tense] = $autoConjugation;
            }
        }

        $this->setInflections($inflections);

        return $this;
    }
}
