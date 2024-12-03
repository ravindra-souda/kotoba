<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Verb;
use App\Repository\VerbRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchVerbByCode extends AbstractController
{
    /**
     * @param VerbRepository<object> $repository
     */
    public function __construct(private VerbRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Verb
    {
        return $this->repository->getVerbByCode($code);
    }
}
