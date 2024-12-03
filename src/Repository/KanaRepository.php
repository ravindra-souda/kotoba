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
        /** @var class-string<T> $className */
        $className = Kana::class;

        parent::__construct($registry, $className);
    }

    public function getKanaByCode(string $code): Kana
    {
        /** @var Kana */
        return $this->getDocByCode($code, Kana::class);
    }
}
