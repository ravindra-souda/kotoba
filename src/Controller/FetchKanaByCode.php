<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Kana;
use App\Exception\NotFoundException;
use App\Repository\KanaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchKanaByCode extends AbstractController
{
    /**
     * @param KanaRepository<object> $repository
     */
    public function __construct(private KanaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(string $code): Kana
    {
        $kana = $this->repository->findOneBy(['code' => $code]);
        if (!$kana instanceof Kana) {
            throw new NotFoundException();
        }

        return $kana;
    }
}
