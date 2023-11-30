<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @template T of object
 *
 * @template-extends ServiceDocumentRepository<T>
 */
abstract class AbstractKotobaRepository extends ServiceDocumentRepository
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
