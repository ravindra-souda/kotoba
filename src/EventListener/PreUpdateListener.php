<?php

declare(strict_types=1);

namespace App\EventListener;

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
        $doc = $args->getDocument();
        $class = $dm->getClassMetadata(get_class($doc));

        $this->setUpdatedAt($doc);
        $this->slugifyCode($doc);

        // persist any changes made
        $dm
            ->getUnitOfWork()
            ->recomputeSingleDocumentChangeSet($class, $doc)
        ;
    }

    private function setUpdatedAt(mixed $doc): void
    {
        $doc->setUpdatedAt(new \DateTimeImmutable());
    }
}
