<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Deck;
use App\Exception\NotFoundException;
use App\Exception\NotProcessablePayloadException;
use App\Repository\DeckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchDeckByCode extends AbstractController
{
    public const VALIDATION_ERR_MISSING_ATID = 
        '@id: field must be a valid resource identifier';

    /**
     * @param DeckRepository<object> $repository
     */
    public function __construct(private DeckRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code, Deck $data): Deck
    {
        var_dump($data);
        if (!isset($data['@id'])) {
            throw new NotProcessablePayloadException(
                self::VALIDATION_ERR_MISSING_ATID
            );
        }

        $deck = $this->repository->findOneBy(['code' => $code]);
        if (!$deck instanceof Deck) {
            throw new NotFoundException();
        }

        return $deck;
    }
}
