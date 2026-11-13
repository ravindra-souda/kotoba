<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Metadata\QueryParameter;

abstract class AbstractKotobaFilter
{
    final protected function getTrimmedValue(QueryParameter $parameter)
    : array|string
    {
        $value = $parameter->getValue();
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            return array_map('trim', $value);
        }

        throw new \Exception(
            'Filter parameter value must be a string or an array, got '
            .gettype($value)
        );
    }
}
