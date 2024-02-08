<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Document\Adjective;
use App\Document\Deck;
use App\Document\Kana;
use App\Document\Kanji;
use App\Document\Noun;
use App\Document\Verb;

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
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Adjective|Deck|Kana|Kanji|Noun|Verb|null {
        if ($operation instanceof DeleteOperationInterface) {
            return $this
                ->removeProcessor
                ->process($data, $operation, $uriVariables, $context)
            ;
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
