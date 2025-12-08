<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\{Post, Put};
use ApiPlatform\Validator\ValidatorInterface;
use App\Document\{Adjective, Deck, Kana, Kanji, Noun, Verb};
use App\Document\Dto\DeckInput;
use App\Exception\CardsNotFoundException;
use Doctrine\ODM\MongoDB\DocumentManager;

final class DtoToDeckHelper
{
    public function __construct(
        private DocumentManager $dm,
        private ValidatorInterface $validator,
        private IriConverterInterface $iriConverter
    ) {}

    private const CARDS_CLASSES = [
        '/api/cards/adjectives' => Adjective::class,
        '/api/cards/kana' => Kana::class,
        '/api/cards/kanji' => Kanji::class,
        '/api/cards/nouns' => Noun::class,
        '/api/cards/verbs' => Verb::class,
    ];

    public function processDeckFromDto(
        mixed $data, Operation $operation, array $uriVariables): Deck
    {
        $deck = $this->fetchDeck($operation, $uriVariables);
        $deck = $this->associateCardsToDeck($operation, $deck, $data);
        $this->validator->validate($deck);

        return $deck;
    }

    private function fetchDeck(Operation $operation, array $uriVariables): Deck
    {        
        if ($operation instanceof Post) {
            return new Deck();
        }

        if ($operation instanceof Put && isset($uriVariables['code'])) {
            return $this
                ->dm
                ->getRepository(Deck::class)
                ->findDeckByCode($uriVariables['code'])
            ;
        }

        throw new \Exception('Method not allowed');
    }

    private function associateCardsToDeck(
        Operation $operation, Deck $deck, DeckInput $data
    ): Deck
    {
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

    private function getIriFromCards(Deck $deck): array
    {
        $iris = [];
        foreach ($deck->getCards() as $card) {
            $iris[] = $this->iriConverter->getIriFromResource($card);
        }

        return $iris;
    }

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