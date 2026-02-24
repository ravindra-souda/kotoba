<?php

declare(strict_types=1);

namespace App\EventListener\Trait;

use App\Document\AbstractKotobaDocument as Doc;
use Cocur\Slugify\Slugify;

trait SlugifyCodeTrait
{
    private Slugify $slugify;

    private function slugifyCode(Doc $doc): static
    {
        $slug = $this->slugify->slugify($doc->getSlugReference());
        $doc->setCode($doc->getIncrement().'-'.$slug)
            ->setSlug($slug)
        ;

        return $this;
    }
}
