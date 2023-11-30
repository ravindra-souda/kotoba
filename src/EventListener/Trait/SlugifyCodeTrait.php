<?php

declare(strict_types=1);

namespace App\EventListener\Trait;

use App\Document\Deck;
use Cocur\Slugify\Slugify;

trait SlugifyCodeTrait
{
    private Slugify $slugify;

    private function slugifyCode(Deck $doc): static
    {
        $code = $doc->getIncrement().'-'.$doc->getTitle();
        $doc->setCode($this->slugify->slugify($code));

        return $this;
    }
}
