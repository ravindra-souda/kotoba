<?php

declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractKotobaDocument
{
    public const VALIDATION_ERR_EMPTY =
        'cannot be left empty';

    public const VALIDATION_ERR_MAXLENGTH =
        'cannot not be longer than {{ limit }} characters';

    public const VALIDATION_ERR_ENUM =
        'must be one of these: {{ choices }}';

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $createdAt = null;

    /** set by MongoDB */
    #[Groups('read')]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ApiProperty(identifier: false)]
    #[Groups('read')]
    #[MongoDB\Id(strategy: 'AUTO', type: 'object_id')]
    protected string $id;

    #[MongoDB\Field(type: 'int')]
    protected int $increment;

    abstract public function finalizeTasks(): self;

    /**
     * @return array<string,array<string,array<string>>>
     */
    abstract public static function getFields(): array;

    abstract public function getSlugReference(): string;

    final public function trimFields(): static
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIncrement(): int
    {
        return $this->increment;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // see App\EventListener\PrePersistListener
    public function setCreatedAt(\DateTimeImmutable $date): self
    {
        $this->createdAt = $date;

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setIncrement(int $increment): self
    {
        $this->increment = $increment;

        return $this;
    }

    // see App\EventListener\PreUpdateListener
    public function setUpdatedAt(\DateTimeImmutable $date): self
    {
        $this->updatedAt = $date;

        return $this;
    }

    /**
     * @param array<string,mixed> $value
     */
    final protected function setLowerAndTrimmedOrNull(
        string $prop,
        string|array|null $value,
        bool $nullable = true,
    ): static {
        if (!property_exists($this, $prop)) {
            throw new \Exception("property {$prop} not found");
        }

        if (is_string($value)) {
            // lower and trimmed or null if nullable for empty strings
            $value = trim(strtolower($value)) ?: ($nullable ? null : '');
        }

        if (is_array($value)) {
            $isEmpty = true;
            array_walk_recursive(
                $value,
                function (&$v) use (&$isEmpty) {
                    $v = trim(strtolower($v));
                    if ($isEmpty && '' !== $v) {
                        $isEmpty = false;
                    }
                }
            );
            // null if there are only empty values
            if ($nullable && $isEmpty) {
                $value = null;
            }
        }

        $this->{$prop} = $value;

        return $this;
    }
}
