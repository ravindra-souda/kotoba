<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Document\{Adjective, Card, Deck, Kana, Kanji, Noun, Verb};
use App\Dto\DeckDto;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @template T
 *
 * @template-implements ProcessorInterface<T>
 */
final class SaveProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<T> $persistProcessor
     * @param ProcessorInterface<T> $removeProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private ProcessorInterface $removeProcessor,
        private DocumentManager $dm,
    ) {}

    private const CARDS_CLASSES = [
        '/api/cards/adjectives' => Adjective::class,
        '/api/cards/kana' => Kana::class,
        '/api/cards/kanji' => Kanji::class,
        '/api/cards/nouns' => Noun::class,
        '/api/cards/verbs' => Verb::class,
    ];

    private function associateCardsToDeck(DeckDto $data): Deck
    {
        $deck = new Deck();
        $deck
            ->setTitle($data->title)
            ->setDescription($data->description)
            ->setType($data->type)
            ->setColor($data->color)
        ;

        foreach ($data->cards as $iri) {
            $code = basename($iri);
            try {
                $className = self::CARDS_CLASSES[dirname($iri)];
            } catch (\Throwable $e) {
                throw new \Exception('Unknown Card type');
            }
            
            $card = $this
                ->dm
                ->getRepository($className)
                ->findOneBy(['code' => $code])
            ;

            if (!$card instanceof $className) {
                throw new \Exception('Card not found');
            }

            $deck->addCard($card);
        }
        return $deck;
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Adjective|Deck|Kana|Kanji|Noun|Verb|DeckDto|null {
        if ($operation instanceof DeleteOperationInterface) {
            return $this
                ->removeProcessor
                ->process($data, $operation, $uriVariables, $context)
            ;
        }

        if ($data instanceof DeckDto) {
            $data = $this->associateCardsToDeck($data);
        }

        $data
            ->trimFields()
            ->finalizeTasks()
        ;

        return $this
            ->persistProcessor
            ->process($data, $operation, $uriVariables, $context)
        ;
    }
}
