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
        new Post(uriTemplate: '/cards/kanji'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    //processor: DeckSaveProcessor::class,
)]
#[MongoDB\Document]
class Kanji extends Card
{
    use Trait\MeaningTrait;

    public const KUNYOMI_MAXLENGTH = 100;

    public const ONYOMI_MAXLENGTH = 100;

    public const VALIDATION_ERR_KANJI = 'must be written using only kanji';

    /** Must be written using only kanji */
    #[Assert\NotBlank(message: Card::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: 1,
        maxMessage: Card::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $kanji;

    /** Must be written using only latin characters, 
     *  will be converted to hiragana by the API */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::KUNYOMI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $kunyomi;

    /** Must be written using only latin characters, 
     *  will be converted to katakana by the API */
    #[Assert\NotBlank(message: self::VALIDATION_ERR_EMPTY)]
    #[Assert\Length(
        max: self::ONYOMI_MAXLENGTH,
        maxMessage: self::VALIDATION_ERR_MAXLENGTH,
    )]
    #[Groups(['read', 'write'])]
    #[MongoDB\Field(type: 'string')]
    protected string $onyomi;

    public function getKanji(): string
    {
        return $this->kanji;
    }

    public function getKunyomi(): string
    {
        return $this->kunyomi;
    }

    public function getOnyomi(): string
    {
        return $this->onyomi;
    }

    public static function isValidKanji(string $string): bool
    {
        // must be kanji only
        return preg_match('/^\p{Han}$/um', $string) === 1;
    }

    public function setKanji(string $kanji): Kanji
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function setKunyomi(string $kunyomi): Kanji
    {
        $this->kunyomi = $kunyomi;

        return $this;
    }

    public function setOnyomi(string $onyomi): Kanji
    {
        $this->onyomi = $onyomi;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        $fields = parent::getFields();
        $fields['string'] = [...$fields['string'], 'kunyomi', 'onyomi'];
        
        return $fields;
    }
}
