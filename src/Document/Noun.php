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
        new Post(uriTemplate: '/cards/nouns'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    //processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document]
class Noun extends Card
{
    use Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait;
    
    public const ALLOWED_BIKAGO = [
        'お',
        'ご',
    ];

    public const HIRAGANA_MAXLENGTH = 30;

    public const KATAKANA_MAXLENGTH = 30;

    /** Must be written using only hiragana */
    #[Assert\Choice(
        choices: self::ALLOWED_BIKAGO,
        message: self::VALIDATION_ERR_ENUM,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $bikago;

    /** Should be set to 'noun' to create a Noun flashcard */
    #[Groups(['read', 'write'])]
    #[MongoDB\Field]
    protected string $type = 'noun';

    public function getBikago(): ?string
    {
        return $this->bikago;
    }

    public function setBikago(?string $bikago): Noun
    {
        $this->bikago = $bikago;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        $fields = parent::getFields();
        $fields['enum']['bikago'] = self::ALLOWED_BIKAGO;
        
        return $fields;
    }
}
