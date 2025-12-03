<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use App\Document\Kanji;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class YomiFilter extends AbstractFilter
{
    /**
     * This function is only used to hook in documentation generators
     * (supported by Swagger and Hydra).
     *
     * @return array<mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description['kunyomi'] = [
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
        $description['onyomi'] = [
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

        if (!in_array($property, ['kunyomi', 'onyomi'])) {
            return;
        }

        $script = Kanji::detect($value);

        /*  values entered for kunyomi are forced to hiragana,
            or nulled if they're not in romaji/hiragana
        */
        if ('kunyomi' === $property) {
            $value = in_array(
                $script,
                [Kanji::ROMAJI, Kanji::HIRAGANA]
            ) ? Kanji::toHiragana($value) : null;
        }

        /*  values entered for onyomi are forced to katakana,
            or nulled if they're not in romaji/katakana
        */
        if ('onyomi' === $property) {
            $value = in_array(
                $script,
                [Kanji::ROMAJI, Kanji::KATAKANA]
            ) ? Kanji::toKatakana($value, false) : null;
        }

        $aggregationBuilder
            ->match()
            ->field($property)
            ->in([$value])
        ;
    }
}
