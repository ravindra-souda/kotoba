<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Kanji;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends AbstractKotobaRepository<T>
 */
class KanjiRepository extends AbstractKotobaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var class-string<T> $className */
        $className = Kanji::class;

        parent::__construct($registry, $className);
    }

    public function getKanjiByCode(string $code): Kanji
    {
        /** @var Kanji */
        return $this->getDocByCode($code, Kanji::class);
    }
}
