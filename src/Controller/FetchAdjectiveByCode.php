<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Adjective;
use App\Exception\NotFoundException;
use App\Repository\AdjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchAdjectiveByCode extends AbstractController
{
    /**
     * @param AdjectiveRepository<object> $repository
     */
    public function __construct(private AdjectiveRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Adjective
    {
        $adjective = $this->repository->findOneBy(['code' => $code]);
        if (!$adjective instanceof Adjective) {
            throw new NotFoundException();
        }

        return $adjective;
    }
}
