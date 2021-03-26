<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
use PhpGuild\MediaObjectBundle\Service\ResolveMediaObject;

/**
 * Class MediaObjectSubscriber
 */
final class MediaObjectSubscriber implements EventSubscriber
{
    /** @var ResolveMediaObject $resolveMediaObject */
    private $resolveMediaObject;

    /**
     * MediaObjectSubscriber constructor.
     *
     * @param ResolveMediaObject $resolveMediaObject
     */
    public function __construct(ResolveMediaObject $resolveMediaObject)
    {
        $this->resolveMediaObject = $resolveMediaObject;
    }

    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * postLoad
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        $this->resolveMediaObject->prepare($entity);
    }

    /**
     * prePersist
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        $this->resolveMediaObject->persist($entity);
    }

    /**
     * preUpdate
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        $this->resolveMediaObject->persist($entity, $eventArgs->getEntityChangeSet());
    }
}
