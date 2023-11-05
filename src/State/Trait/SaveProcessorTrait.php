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
            $nextIncrement = count($this->repository->findAll()) + 1;
        } catch (\Throwable $e) {
            throw new \Exception('Error during code slugify');
        }

        $data->setCode(
            $this->slugify->slugify($nextIncrement.'-'.$data->getTitle())
        );
    }
}
