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

    /** Slugified by the API from reference field */
    #[ApiProperty(identifier: true)]
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Field(type: 'string')]
    protected string $code = '';

    /** set by MongoDB */
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $createdAt = null;

    /** set by MongoDB */
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Field(type: 'date_immutable')]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ApiProperty(identifier: false)]
    #[Groups(['card:read', 'deck:read'])]
    #[MongoDB\Id(strategy: 'AUTO', type: 'object_id')]
    protected string $id;

    #[MongoDB\Field(type: 'int')]
    protected int $increment;

    #[MongoDB\Field(type: 'string')]
    protected string $slug;

    abstract public function finalizeTasks(): static;

    /**
     * @return array<string,array<string,array<string>>>
     */
    abstract public static function getFields(): array;

    abstract public function getSlugReference(): string;

    public function onDelete(): void {}

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

        $message = preg_replace('/{{ [a-z]+ }}/i', $replacement, $message, 1);

        if (null === $message) {
            throw new \Exception('Error when formatting message');
        }

        return $message;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setCreatedAt(\DateTimeImmutable $date): static
    {
        $this->createdAt = $date;

        return $this;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    // see App\EventListener\PrePersistListener
    public function setIncrement(int $increment): static
    {
        $this->increment = $increment;

        return $this;
    }

    // see App\EventListener\Trait\SlugifyCodeTrait
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    // see App\EventListener\PreUpdateListener
    public function setUpdatedAt(\DateTimeImmutable $date): static
    {
        $this->updatedAt = $date;

        return $this;
    }

    /** @param array<string> $iris
     *
     * @return array<string>
     */
    final public static function sortByIri(array $iris): array
    {
        usort(
            $iris,
            fn (string $i1, string $i2) => preg_replace('/\d+-/', '', $i1)
                <=> preg_replace('/\d+-/', '', $i2)
        );

        return $iris;
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
