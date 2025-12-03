<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyInfo\Type;

final class WithInflectionsFilter extends AbstractFilter
{
    /**
     * This function is only used to hook in documentation generators
     * (supported by Swagger and Hydra).
     *
     * @return array<mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        $description['hiragana'] = [
            'property' => 'hiragana',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
        ];
        $description['kanji'] = [
            'property' => 'kanji',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
        ];
        $description['katakana'] = [
            'property' => 'katakana',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
        ];

        return $description;
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
}
