<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use App\Document\Noun;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyInfo\Type;

final class WithBikagoFilter extends AbstractKotobaFilter
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
        if (!in_array($property, ['hiragana', 'kanji'])) {
            return;
        }

        $bikago = null;
        $value = $this->getTrimmedValue($parameter);
        foreach (Noun::ALLOWED_BIKAGO as $allowedBikago) {
            if (str_starts_with($value, $allowedBikago)) {
                $bikago = $allowedBikago;

                break;
            }
        }
        $valueWithoutBikago = substr($value, strlen($bikago ?? ''));

        $regexp = 'hiragana' === $property ?
            new Regex("^{$value}") : new Regex($value);

        $regexpWithoutBikago = 'hiragana' === $property ?
            new Regex("^{$valueWithoutBikago}")
            : new Regex($valueWithoutBikago);

        $aggregationBuilder
            ->match()
                // search = おんが where hiragana = おんがく and bikago = null
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field($property)
                    ->equals($regexp)
                    ->field('bikago')
                    ->equals(null)
            )
                // search = おかし where hiragana = かし and bikago = お
            ->addOr(
                $aggregationBuilder
                    ->matchExpr()
                    ->field($property)
                    ->equals($regexpWithoutBikago)
                    ->field('bikago')
                    ->equals($bikago)
            )
        ;
    }
}
