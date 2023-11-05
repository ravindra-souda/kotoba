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

    /**
     * @param array<string> $value
     */
    final public static function formatMsg(
        string $message,
        int|string|array $value
    ): string {
        $replacement = (is_array($value)) ?
            '"'.implode('", "', $value).'"' : (string) $value;

        return preg_replace('/{{ [a-z]+ }}/i', $replacement, $message, 1);
    }
}
