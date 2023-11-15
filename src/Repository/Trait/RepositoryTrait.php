<?php

declare(strict_types=1);

namespace App\Repository\Trait;

trait RepositoryTrait
{
    public function getNextIncrement(string $class): int
    {
        $doc = $this->findOneBy([], ['increment' => 'DESC']);
        if (!$doc instanceof $class) {
            return 1;
        }

        return $doc->getIncrement() + 1;
    }
}
