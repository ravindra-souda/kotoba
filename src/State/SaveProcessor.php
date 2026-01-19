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
use App\Document\Card;
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

        // $noun = $this->dm->getRepository(Noun::class)->findOneBy(['hiragana' => 'いぬ']); // ok
        $noun = $this->dm->getRepository(Noun::class)->findOneBy(['hiragana' => 'いち']);
        $deck = $this->dm->getRepository(Deck::class)->findOneBy(['title' => 'dummy']);
        //var_dump(get_class($data));
        if ($noun !== null) {
            //var_dump([$noun->getHiragana(), get_class($data)]);
            //var_dump(get_class($data));
            //$code = $noun->getCode();
            //var_dump($code);
        } else {
            var_dump('lol:'.get_class($data));
        }

        if ($data instanceof DeckDto) {
            $deck = new Deck();
            $deck
                ->setTitle($data->title)
                ->setDescription($data->description)
                ->setType($data->type)
                ->setColor($data->color)
            ;

            //var_dump($data->cards);
            foreach ($data->cards as $iri) {
                //var_dump($iri);
                //$card = $this->dm->getRepository(Noun::class)->find();
                $card = $this->dm->getRepository(Noun::class)->findOneBy(['hiragana' => 'いち']);
                var_dump([$card->getHiragana(), $iri]);
                $deck->addCard($card);
            }
            $data = $deck;
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
