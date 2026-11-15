<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Metadata\QueryParameter;

abstract class AbstractKotobaFilter
{
    final protected function getLowerAndTrimmedValue(QueryParameter $parameter)
    : array|string
    {
        $value = $parameter->getValue();
        if (is_string($value)) {
            return mb_strtolower(trim($value));
        }

        if (is_array($value)) {
            return array_map(fn($v) => $v = mb_strtolower(trim($v)), $value);
        }

        throw new \Exception(
            'Filter parameter value must be a string or an array, got '
            .gettype($value)
        );
    }
}
