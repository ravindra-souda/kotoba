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

        // $property = $parameter->getProperty();

        // if ('meaning' !== $property) {
        //     return;
        // }

        $key = $parameter->getKey();
        $properties = $parameter->getProperties();
        $value = $this->getLowerAndTrimmedValue($parameter);
        dump($key, $value, $properties);
        return;

        if (!array_key_exists('lang', $value)
            || !array_key_exists('search', $value)
        ) {
            return;
        }

        ['lang' => $lang, 'search' => $search] = $value;

        // $elemMatch is needed to search in nested arrays
        $aggregationBuilder
            ->match()
            ->field('meaning.'.$lang)
            ->elemMatch(
                ['$elemMatch' => ['$in' => [$search],
                ],
                ]
            )
        ;
    }
}
