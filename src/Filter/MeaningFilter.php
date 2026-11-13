<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class MeaningFilter extends AbstractKotobaFilter
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
        if ('meaning' !== $property) {
            return;
        }

        $value = $this->getTrimmedValue($parameter);
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
                ['$elemMatch' => ['$in' => [trim(strtolower($search))],
                ],
                ]
            )
        ;
    }
}
