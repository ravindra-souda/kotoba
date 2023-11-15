<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Document\Deck;
use App\Repository\DeckRepository;

/**
 * @template T
 *
 * @template-implements ProcessorInterface<T>
 */
final class DeckSaveProcessor implements ProcessorInterface
{
    use Trait\SaveProcessorTrait;

    /**
     * @param DeckRepository<object> $repository
     * @param ProcessorInterface<T>  $persistProcessor
     * @param ProcessorInterface<T>  $removeProcessor
     */
    public function __construct(
        private DeckRepository $repository,
        private ProcessorInterface $persistProcessor,
        private ProcessorInterface $removeProcessor,
        \Cocur\Slugify\SlugifyInterface $slugify,
    ) {
        /** @var \Cocur\Slugify\Slugify $slugify */
        $this->slugify = $slugify;
    }

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
        $this->slugifyCode($data);

        return $this
            ->persistProcessor
            ->process($data, $operation, $uriVariables, $context)
        ;
    }
}
