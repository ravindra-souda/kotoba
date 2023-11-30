<?php

declare(strict_types=1);

namespace App\EventListener\Trait;

use Cocur\Slugify\Slugify;

trait SlugifyCodeTrait
{
    private Slugify $slugify;

    private function slugifyCode(mixed $doc): void
    {
        $doc
            ->setCode(
                $this->slugify->slugify(
                    $doc->getIncrement().'-'.$doc->getTitle()
                )
            )
        ;
    }
}
