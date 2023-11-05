<?php

declare(strict_types=1);

namespace App\Document;

abstract class AbstractKotobaDocument
{
    /**
     * @return array<string, mixed>
     */
    abstract public static function getFields(): array;

    public function trimFields(): void
    {
        foreach ($this->getFields()['string'] as $field) {
            $this->{$field} = trim($this->{$field} ?? '');
        }
    }
}
