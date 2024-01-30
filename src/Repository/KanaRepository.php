<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Kana;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class KanaRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kana::class);
    }
}
