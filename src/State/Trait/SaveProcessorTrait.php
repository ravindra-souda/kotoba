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
            /** @var \App\Repository\DeckRepository<object> $repo */
            $repo = $this->repository;

            /** @var class-string $className */
            $className = get_class($data);
            $nextIncrement = $repo->getNextIncrement($className);
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
