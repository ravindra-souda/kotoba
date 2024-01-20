<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(uriTemplate: '/cards/verbs'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    //processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document]
class Verb extends Card
{
    use Trait\GroupTrait, 
        Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait,
        Trait\Const\VerbTrait;

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

    /** dictionary must be filled, 
     *  any value left empty will be completed by automatic conjugation */
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
                ]
            ],
            'imperative' => [
                'affirmative' => '',
                'negative' => '',
            ]
    ];

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

        if ($this->group !== self::IRREGULAR 
            && in_array($dict, self::IRREGULAR_VERBS)) {
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
            return $this->conjugateIrregular();
        }
    }

    private function conjugateIchidan(): Verb
    {
        $inflections = $this->getInflections();
        $root = mb_substr($inflections['dictionary'], 0, -1);

        $autoConjugations = self::ICHIDAN_INFLECTIONS;
        array_walk_recursive(
            $autoConjugations,
            fn(&$v) => $v = $root.$v,
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
            ->setReviewed(true);
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
            fn(&$v) => $v = str_replace($vowels, $okurigana, $root.$v),
        );

        return $this->fillEmptyInflections($autoConjugations);
    }

    private function fillEmptyInflections(array $autoConjugations): Verb
    {
        $inflections = $this->getInflections();

        $inflections = array_replace_recursive($autoConjugations, $inflections);
        $inflections['non-past']['informal']['affirmative'] = 
            $inflections['dictionary'];

        return $this
            ->setInflections($inflections)
            ->setReviewed(false);
    }
}
