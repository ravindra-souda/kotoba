<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Document\Deck;

/**
 * @template T
 *
 * @template-implements ProcessorInterface<T>
 */
final class DeckSaveProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<T> $persistProcessor
     * @param ProcessorInterface<T> $removeProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private ProcessorInterface $removeProcessor,
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ?Deck {
        if ($operation instanceof DeleteOperationInterface) {
            return $this
                ->removeProcessor
                ->process($data, $operation, $uriVariables, $context)
            ;
        }

        $data->trimFields();

        return $this
            ->persistProcessor
            ->process($data, $operation, $uriVariables, $context)
        ;
    }
}
