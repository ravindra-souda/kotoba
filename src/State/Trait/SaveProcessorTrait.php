<?php

declare(strict_types=1);

namespace App\State\Trait;

use Cocur\Slugify\Slugify;

trait SaveProcessorTrait
{
    private Slugify $slugify;

    private function slugifyCode(mixed $data): void
    {
        try {
            $nextIncrement = $this
                ->repository
                ->getNextIncrement(get_class($data))
            ;
        } catch (\Throwable $e) {
            throw new \Exception('Error during code slugify');
        }

        $data
            ->setIncrement($nextIncrement)
            ->setCode(
                $this->slugify->slugify($nextIncrement.'-'.$data->getTitle())
            )
        ;
    }
}
