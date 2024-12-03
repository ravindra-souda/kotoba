<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Verb;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class VerbRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var class-string<T> $className */
        $className = Verb::class;

        parent::__construct($registry, $className);
    }

    public function getVerbByCode(string $code): Verb
    {
        /** @var Verb */
        return $this->getDocByCode($code, Verb::class);
    }
}
