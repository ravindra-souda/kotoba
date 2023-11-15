<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Deck;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @template T of object
 *
 * @template-extends ServiceDocumentRepository<T>
 */
class DeckRepository extends ServiceDocumentRepository
{
    use Trait\RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }
}
