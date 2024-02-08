<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Noun;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class NounRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Noun::class);
    }
}
