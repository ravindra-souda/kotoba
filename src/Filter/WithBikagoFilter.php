<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use App\Document\Noun;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyInfo\Type;

final class WithBikagoFilter extends AbstractFilter
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
        
        if (!in_array($property, ['hiragana', 'kanji'])) {
            return;
        }

        $bikago = null;
        foreach (Noun::ALLOWED_BIKAGO as $allowedBikago) {
            if (str_starts_with($value, $allowedBikago)) {
                $bikago = $allowedBikago;
                break;
            }
        }
        $valueWithoutBikago = substr($value, strlen($bikago ?? ''));
        
        $regexp = $property === 'hiragana' ? 
            new Regex("^$value") : new Regex($value);

        $regexpWithoutBikago = $property === 'hiragana' ? 
            new Regex("^$valueWithoutBikago") : new Regex($valueWithoutBikago);

        $aggregationBuilder
            ->match()
                ->field($property)
                ->equals($regexpWithoutBikago)
                ->field('bikago')
                ->equals($bikago);
                /*
                ->field($property)
                ->equals($regexp)
                ->addOr(
                    $aggregationBuilder
                    ->expr()
                    ->field($property)
                    ->eq($property, $regexpWithoutBikago)
                );
                //->field($property)
                //->equals($regexpWithoutBikago)
                //->field('bikago')
                //->equals($bikago);
                /*
                ->field($property)
                ->equals($regexpWithoutBikago)
                ->field('bikago')
                ->equals($bikago);
                */
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        return [];
        if (!$this->properties) {
            return [];
        }

        $description["kunyomi"] = [
            'property' => 'kunyomi',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => 'can be written in romaji or hiragana',
            'openapi' => [
                'example' => 'amatsu',
                'allowReserved' => false,
                'allowEmptyValue' => false,
                'explode' => false, 
            ],
        ];
        $description["onyomi"] = [
            'property' => 'onyomi',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => 'can be written in romaji or katakana',
            'openapi' => [
                'example' => 'テン',
                'allowReserved' => false,
                'allowEmptyValue' => false,
                'explode' => false,
            ],
        ];

        return $description;
    }
}