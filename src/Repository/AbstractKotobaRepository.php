<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\AbstractKotobaDocument as Doc;
use App\Document\Adjective;
use App\Document\Deck;
use App\Document\Kana;
use App\Document\Kanji;
use App\Document\Noun;
use App\Document\Verb;
use App\Exception\NotFoundException;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @template T of object
 *
 * @template-extends ServiceDocumentRepository<T>
 */
abstract class AbstractKotobaRepository extends ServiceDocumentRepository
{
    /**
     * @param class-string $class
     */
    public function getNextIncrement(string $class): int
    {
        /** @var \App\Document\Deck $doc */
        $doc = $this->findOneBy([], ['increment' => 'DESC']);
        if (!$doc instanceof $class) {
            return 1;
        }

        return $doc->getIncrement() + 1;
    }

    /**
     * @param class-string $class
     */
    protected function getDocByCode(string $code, string $class): Doc
    {
        /** @var Adjective|Deck|Kana|Kanji|Noun|Verb $doc */
        $doc = $this->findOneBy(['code' => $code]);
        if (!$doc instanceof $class) {
            throw new NotFoundException();
        }

        return $doc;
    }
}
