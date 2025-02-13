<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class MeaningFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, Builder $aggregationBuilder, string $resourceClass, Operation $operation = null, array &$context = []): void
    {
        // Otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        //$parameterName = $queryNameGenerator->generateParameterName($property); // Generate a unique parameter name to avoid collisions with other filters
        ['lang' => $lang, 'search' => $search] = $value;
        var_dump($lang, $search);
        $aggregationBuilder
            ->match()
                ->field('meaning.'.$lang)
                //->field('meaning.en')
                //->in([$search]);
                //->in(['/$search/']);
                ->in(['brief; quick; light']);
                //->in(['/qui/']);
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["regexp_$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter using a regex. This will appear in the OpenApi documentation!',
                'openapi' => [
                    'example' => 'Custom example that will be in the documentation and be the default value of the sandbox',
                    'allowReserved' => false,// if true, query parameters will be not percent-encoded
                    'allowEmptyValue' => true,
                    'explode' => false, // to be true, the type must be Type::BUILTIN_TYPE_ARRAY, ?product=blue,green will be ?product=blue&product=green
                ],
            ];
        }

        return $description;
    }
}