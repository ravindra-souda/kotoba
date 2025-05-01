<?php

namespace App\Controller;

use App\Document\{Adjective, Noun, Verb};
use App\Exception\NotFoundException;
use App\Repository\{AdjectiveRepository, NounRepository, VerbRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FetchCards extends AbstractController
{
    /**
     * @param VerbRepository<object> $repository
     */
    public function __construct(
        private AdjectiveRepository $adjectiveRepository,
        private NounRepository $nounRepository,
        private VerbRepository $verbRepository,
    )
    {
        $this->adjectiveRepository = $adjectiveRepository;
        $this->nounRepository = $nounRepository;
        $this->verbRepository = $verbRepository;
    }

    public function __invoke(Adjective|Noun|Verb $card): Adjective|Noun|Verb
    {
        $this->bookPublishingHandler->handle($book);

        return $book;
    }
}