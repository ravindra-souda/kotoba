<?php

declare(strict_types=1);

namespace App\Document;

final class Noun extends Card
{
    use Trait\HiraganaTrait, 
        Trait\KanjiTrait, 
        Trait\KatakanaTrait, 
        Trait\MeaningTrait;
    
    public const ALLOWED_BIKAGO = [
        'お',
        'ご',
    ];

    protected string $bikago;

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
