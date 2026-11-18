<?php

declare(strict_types=1);

namespace App\Filter\Workaround;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use App\Filter\AbstractKotobaFilter;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

/**
 * @deprecated Workaround for SortFilter on multiple fields
 */
final class MultiSortFilter extends AbstractKotobaFilter
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

        $properties = $parameter->getProperties();
        $value = $this->getLowerAndTrimmedValue($parameter);
        if (!is_array($properties) || !is_array($value)) {
            return;
        }

        $sort = [];
        foreach ($value as $field => $direction) {
            if (!in_array($field, $properties)) {
                continue;
            }
            $direction = strtolower($direction);
            if (!in_array($direction, ['asc', 'desc'])) {
                continue;
            }
            $sort[$field] = $direction;
        }

        if (!empty($sort)) {
            $aggregationBuilder->sort($sort);
        }

    }
}
