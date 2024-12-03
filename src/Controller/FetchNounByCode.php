<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Noun;
use App\Repository\NounRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchNounByCode extends AbstractController
{
    /**
     * @param NounRepository<object> $repository
     */
    public function __construct(private NounRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Noun
    {
        return $this->repository->getNounByCode($code);
    }
}
