<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Kanji;
use App\Repository\KanjiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchKanjiByCode extends AbstractController
{
    /**
     * @param KanjiRepository<object> $repository
     */
    public function __construct(private KanjiRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Kanji
    {
        return $this->repository->getKanjiByCode($code);
    }
}
