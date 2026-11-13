<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Odm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use App\Document\Kanji;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class YomiFilter extends AbstractKotobaFilter
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
        if (!in_array($property, ['kunyomi', 'onyomi'])) {
            return;
        }

        $value = $this->getTrimmedValue($parameter);

        $script = Kanji::detect($value);

        /*  values entered for kunyomi are forced to hiragana,
            or set to false (no results possible) if they're not in romaji/hiragana
        */
        if ('kunyomi' === $property) {
            $value = in_array(
                $script,
                [Kanji::ROMAJI, Kanji::HIRAGANA]
            ) ? Kanji::toHiragana($value) : false;
        }

        /*  values entered for onyomi are forced to katakana,
            or set to false (no results possible) if they're not in romaji/katakana
        */
        if ('onyomi' === $property) {
            $value = in_array(
                $script,
                [Kanji::ROMAJI, Kanji::KATAKANA]
            ) ? Kanji::toKatakana($value, false) : false;
        }

        $aggregationBuilder
            ->match()
            ->field($property)
            ->in([$value])
        ;
    }
}
