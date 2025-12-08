<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use App\Document\{Adjective, Deck, Kana, Kanji, Noun, Verb};
use App\Document\Dto\DeckInput;


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
        private DtoToDeckHelper $dtoToDeck,
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Adjective|Deck|Kana|Kanji|Noun|Verb|null {
        if ($operation instanceof Delete) {
            $data->onDelete();

            return $this
                ->removeProcessor
                ->process($data, $operation, $uriVariables, $context)
            ;
        }

        if ($data instanceof DeckInput) {
            $data = $this
                ->dtoToDeck
                ->processDeckFromDto($data, $operation, $uriVariables);
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
