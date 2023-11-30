<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Deck;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class DeckRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var class-string<T> $className */
        $className = Deck::class;

        parent::__construct($registry, $className);
    }
}
