<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ParameterNotFound;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyInfo\Type;

final class WithInflectionsFilter extends AbstractKotobaFilter
implements FilterInterface
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

    public function apply(
        Builder $aggregationBuilder,
        string $resourceClass,
        ?Operation $operation = null,
        array &$context = []
    ): void {
        $parameter = $context['parameter'] ?? null;
        if (is_null($parameter)) {
            return;
        }

        $property = $parameter->getProperty();
        if (!in_array($property, ['hiragana', 'katakana', 'kanji'])) {
            return;
        }

        $value = $this->getTrimmedValue($parameter);
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
