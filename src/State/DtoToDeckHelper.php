<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Validator\ValidatorInterface;
use App\Document\Adjective;
use App\Document\Deck;
use App\Document\Dto\DeckInput;
use App\Document\Kana;
use App\Document\Kanji;
use App\Document\Noun;
use App\Document\Verb;
use App\Exception\CardsNotFoundException;
use Doctrine\ODM\MongoDB\DocumentManager;

final class DtoToDeckHelper
{
    private const CARDS_CLASSES = [
        '/api/cards/adjectives' => Adjective::class,
        '/api/cards/kana' => Kana::class,
        '/api/cards/kanji' => Kanji::class,
        '/api/cards/nouns' => Noun::class,
        '/api/cards/verbs' => Verb::class,
    ];

    public function __construct(
        private DocumentManager $dm,
        private ValidatorInterface $validator,
        private IriConverterInterface $iriConverter
    ) {}

    /**
     * @param array<string,string> $uriVariables
     */
    public function processDeckFromDto(
        mixed $data,
        Operation $operation,
        array $uriVariables
    ): Deck {
        $deck = $this->fetchDeck($operation, $uriVariables);
        $deck = $this->associateCardsToDeck($operation, $deck, $data);
        $this->validator->validate($deck);

        return $deck;
    }

    /**
     * @param array<string,string> $uriVariables
     */
    private function fetchDeck(Operation $operation, array $uriVariables): Deck
    {
        if ($operation instanceof Post) {
            return new Deck();
        }

        if ($operation instanceof Put && isset($uriVariables['code'])) {
            /** @var \App\Repository\DeckRepository<object> $repo */
            $repo = $this
                ->dm
                ->getRepository(Deck::class)
            ;

            return $repo->getDeckByCode($uriVariables['code']);
        }

        throw new \Exception('Method not allowed');
    }

    private function associateCardsToDeck(
        Operation $operation,
        Deck $deck,
        DeckInput $data
    ): Deck {
        $cardsBefore = $this->getIriFromCards($deck);

        $deck
            ->setTitle($data->title)
            ->setDescription($data->description)
            ->setType($data->type)
            ->setColor($data->color)
            ->clearCards()
        ;

        $cards = $this->validateCards($data->cards);
        array_walk($cards, [$deck, 'addCard']);

        if ($operation instanceof Post) {
            return $deck;
        }

        $cardsAfter = $this->getIriFromCards($deck);

        /*  triggers PreUpdateListener, since cards collection is not managed
            by Doctrine at this stage in our custom State Processor */
        if ($cardsBefore !== $cardsAfter) {
            $deck->setUpdatedAt(new \DateTimeImmutable());
        }

        return $deck;
    }

    /**
     * @return array<string>
     */
    private function getIriFromCards(Deck $deck): array
    {
        $iris = [];
        foreach ($deck->getCards() as $card) {
            $iri = $this->iriConverter->getIriFromResource($card);
            if (null === $iri) {
                throw new \Exception('Unable to retrieve IRI from Card');
            }
            $iris[] = $iri;
        }

        return $iris;
    }

    /**
     * @param array<string> $iris
     *
     * @return array<Adjective|Kana|Kanji|Noun|Verb>
     */
    private function validateCards(array $iris): array
    {
        $validCards = [];
        $invalidIris = [];

        foreach ($iris as $iri) {
            $code = basename($iri);

            try {
                $className = self::CARDS_CLASSES[dirname($iri)];
            } catch (\Throwable $e) {
                $invalidIris[] = $iri;

                continue;
            }

            $card = $this
                ->dm
                ->getRepository($className)
                ->findOneBy(['code' => $code])
            ;

            if (!$card instanceof $className) {
                $invalidIris[] = $iri;

                continue;
            }

            $validCards[] = $card;
        }

        if (!empty($invalidIris)) {
            throw new CardsNotFoundException($invalidIris);
        }

        return $validCards;
    }
}
