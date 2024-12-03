<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Deck;
use App\Repository\DeckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchDeckByCode extends AbstractController
{
    /**
     * @param DeckRepository<object> $repository
     */
    public function __construct(private DeckRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Deck
    {
        return $this->repository->getDeckByCode($code);
    }
}
