<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Document\Deck;
use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;

#[AsDocumentListener(event: Events::prePersist)]
class PrePersistListener
{
    use Trait\SlugifyCodeTrait;

    public function __construct(
        \Cocur\Slugify\SlugifyInterface $slugify,
    ) {
        /** @var \Cocur\Slugify\Slugify $slugify */
        $this->slugify = $slugify;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $dm = $args->getDocumentManager();

        /** @var Deck $doc */
        $doc = $args->getDocument();

        $this
            ->setCreatedAt($doc)
            ->setIncrement($doc, $dm)
            ->slugifyCode($doc)
        ;
    }

    private function setCreatedAt(Deck $doc): static
    {
        $doc->setCreatedAt(new \DateTimeImmutable());

        return $this;
    }

    private function setIncrement(Deck $doc, DocumentManager $dm): static
    {
        $className = get_class($doc);

        /** @var \App\Repository\AbstractKotobaRepository<object> $repo */
        $repo = $dm->getRepository($className);

        try {
            $nextIncrement = $repo->getNextIncrement($className);
            $doc->setIncrement($nextIncrement);
        } catch (\Throwable $e) {
            throw new \Exception('Error during code slugify');
        }

        return $this;
    }
}
