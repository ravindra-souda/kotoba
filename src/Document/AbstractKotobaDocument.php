<?php

declare(strict_types=1);

namespace App\Document;

abstract class AbstractKotobaDocument
{
    abstract public function finalizeTasks(): static;

    /**
     * @return array<string, mixed>
     */
    abstract public static function getFields(): array;

    abstract public function getSlugReference(): string;

    protected function setLowerAndTrimmedOrNull(
        string $prop, 
        string|array|null $value
    ): static
    {
        if (!property_exists($this, $prop)) {
            throw new \Exception("property {$prop} not found");
        }

        if (is_string($value)) {
            // null if empty string
            $value = trim(strtolower($value)) ?: null;
        }

        if (is_array($value)) {
            array_walk_recursive(
                $value,
                fn(&$v) => $v = trim(strtolower($v)),
            );
        }

        $this->{$prop} = $value;
        return $this;
    }

    public final function trimFields(): static
    {
        foreach ($this->getFields()['string']['trim'] ?? [] as $field) {
            // trim or null if empty string
            $this->{$field} = trim($this->{$field} ?? '') ?: null;
        }

        foreach ($this->getFields()['string']['lower+trim'] ?? [] as $field) {
            $this->setLowerAndTrimmedOrNull($field, $this->{$field});
        }
        
        return $this;
    }

    public static function formatMsg(
        string $message, 
        int|string|array $value
    ): string
    {
        $replacement = (is_array($value)) ? 
            '"'.implode('", "', $value).'"' : (string) $value;

        return preg_replace("/{{ [a-z]+ }}/i", $replacement, $message, 1);
    }
}
