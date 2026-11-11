<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ParameterNotFound;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyInfo\Type;

final class WithInflectionsFilter implements FilterInterface
{
    /**
     * This function is only used to hook in documentation generators
     * (supported by Swagger and Hydra).
     *
     * @return array<mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        return [];
    }

    /**
     * @param array<mixed> $context
     */
    protected function filterProperty(
        string $property,
        mixed $value,
        Builder $aggregationBuilder,
        string $resourceClass,
        ?Operation $operation = null,
        array &$context = []
    ): void {
        // Otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        if (!in_array($property, ['hiragana', 'katakana', 'kanji'])) {
            return;
        }

        $value = trim($value);
        $regexp = 'kanji' === $property ?
            new Regex($value) : new Regex("^{$value}");

        $aggregationBuilder
            ->match()
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field($property)
                    ->equals($regexp)
            )
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field('searchInflections')
                    ->in([$value])
            )
        ;
    }

    public function apply(
        Builder $aggregationBuilder,
        string $resourceClass,
        ?Operation $operation = null,
        array &$context = []
    ): void {
        $parameter = $context['parameter'] ?? null;
        $value = $parameter?->getValue();

        // The parameter may not be present.
        // It's recommended to add validation (e.g., `required: true`) on the Parameter attribute
        // if the filter logic depends on the value.
        if ($value instanceof ParameterNotFound) {
            return;
        }

        dump($parameter);
        $property = $parameter->getProperty() ?? $parameter->getKey();

        if (!in_array($property, ['hiragana', 'katakana', 'kanji'])) {
            return;
        }

        $value = trim($value);
        $regexp = 'kanji' === $property ?
            new Regex($value) : new Regex("^{$value}");

        $aggregationBuilder
            ->match()
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field($property)
                    ->equals($regexp)
            )
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field('searchInflections')
                    ->in([$value])
            )
        ;
    }
}
