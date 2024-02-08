<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Adjective;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class AdjectiveRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var class-string<T> $className */
        $className = Adjective::class;

        parent::__construct($registry, $className);
    }
}
