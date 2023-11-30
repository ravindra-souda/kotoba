<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Document\Deck;
use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;

#[AsDocumentListener(event: Events::preUpdate)]
class PreUpdateListener
{
    use Trait\SlugifyCodeTrait;

    public function __construct(
        \Cocur\Slugify\SlugifyInterface $slugify,
    ) {
        /** @var \Cocur\Slugify\Slugify $slugify */
        $this->slugify = $slugify;
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $dm = $args->getDocumentManager();

        /** @var Deck $doc */
        $doc = $args->getDocument();
        $class = $dm->getClassMetadata(get_class($doc));

        $this
            ->setUpdatedAt($doc)
            ->slugifyCode($doc)
        ;

        // persist any changes made
        $dm
            ->getUnitOfWork()
            ->recomputeSingleDocumentChangeSet($class, $doc)
        ;
    }

    private function setUpdatedAt(Deck $doc): static
    {
        $doc->setUpdatedAt(new \DateTimeImmutable());

        return $this;
    }
}
