<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Deck;
use App\Exception\NotFoundException;
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
        parent::__construct($registry, Deck::class);
    }

    public function findDeckByCode(string $code): Deck
    {
        $deck = $this->findOneBy(['code' => $code]);

        if (!$deck instanceof Deck) {
            throw new NotFoundException();
        }

        return $deck;
    }
}
