<?php

declare(strict_types=1);

namespace App\Repository\Trait;

trait RepositoryTrait
{
    /**
     * @param class-string $class
     */
    public function getNextIncrement(string $class): int
    {
        /** @var \App\Document\Deck $doc */
        $doc = $this->findOneBy([], ['increment' => 'DESC']);
        if (!$doc instanceof $class) {
            return 1;
        }

        return $doc->getIncrement() + 1;
    }
}
