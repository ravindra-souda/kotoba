<?php

declare(strict_types=1);

namespace App\EventListener\Trait;

use Cocur\Slugify\Slugify;

trait SlugifyCodeTrait
{
    private Slugify $slugify;

    private function slugifyCode(mixed $doc): void
    {
        $slug = $this->slugify->slugify($doc->getSlugReference());
        $doc->setCode($doc->getIncrement().'-'.$slug)
            ->setSlug($slug);
    }
}
