<?php

declare(strict_types=1);

namespace App\Operation;

use ApiPlatform\Metadata\Operation\PathSegmentNameGeneratorInterface;
use Doctrine\Inflector\InflectorFactory;

class KotobaPathSegmentNameGenerator implements PathSegmentNameGeneratorInterface
{
    public const SINGULAR_ONLY = ['kana', 'kanji'];

    /**
     * Some resources don't need to be pluralized.
     *
     * @param string $name usually a ResourceMetadata shortname
     *
     * @return string A string that is a part of the route name
     */
    public function getSegmentName(
        string $name,
        bool $collection = true
    ): string {
        $inflector = InflectorFactory::create()->build();

        $urlized = $inflector->urlize($name);

        return in_array($urlized, self::SINGULAR_ONLY) ?
            $urlized : $inflector->pluralize($urlized);
    }
}
