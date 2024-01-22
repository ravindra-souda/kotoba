<?php

declare(strict_types=1);

namespace App\Document;

abstract class AbstractKotobaDocument
{
    /**
     * @return array<string, mixed>
     */
    abstract public static function getFields(): array;

    abstract public function getSlugReference(): string;

    public function trimFields(): void
    {
        foreach ($this->getFields()['string'] as $field) {
            $this->{$field} = trim($this->{$field} ?? '');
        }
    }

    public static function trimArrayValues(array $array): array
    {
        array_walk_recursive(
            $array,
            fn(&$value) => $value = trim(strtolower($value)),
        );

        return $array;
    }

    public function shapeStr(?string $string): ?string
    {
        $string = trim(strtolower($string ?? ''));
        
        return $string === '' ? null : $string;
    }
}
