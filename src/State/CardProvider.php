<?php

namespace App\State;

use App\Document\{Adjective, Noun, Verb};
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class CardProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $collectionProvider)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function provide(
        Operation $operation, array $uriVariables = [], array $context = []
    ): object|array|null
    {
        var_dump('provider', $context['filters']);
        $operation->setClass(Adjective::class);
        $adjective = $this
            ->collectionProvider->provide($operation, $uriVariables, $context);

        //return [new Adjective($context['filters'])];
        
    }
}
