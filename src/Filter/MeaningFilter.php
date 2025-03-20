<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class MeaningFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, Builder $aggregationBuilder, string $resourceClass, ?Operation $operation = null, array &$context = []): void
    {
        // Otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        if ($property !== 'meaning') {
            return;
        }

        if (!array_key_exists('lang', $value) || !array_key_exists('search', $value)) {
            return;
        }

        ['lang' => $lang, 'search' => $search] = $value;

        // $elemMatch is needed to search in nested arrays
        $obj = (object) ['$elemMatch' => ['$in' => [trim(strtolower($search))]]];

        $aggregationBuilder
            ->match()
                ->field('meaning.'.$lang)
                ->elemMatch($obj);
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description["meaning[lang]"] = [
            'property' => 'meaning[lang]',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'openapi' => [
                'example' => 'en',
                'allowReserved' => false,
                'allowEmptyValue' => false,
                'explode' => false, 
            ],
        ];
        $description["meaning[search]"] = [
            'property' => 'meaning[search]',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'openapi' => [
                'example' => 'school',
                'allowReserved' => false,
                'allowEmptyValue' => false,
                'explode' => false,
            ],
        ];

        return $description;
    }
}