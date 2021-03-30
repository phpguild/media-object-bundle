<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
use PhpGuild\MediaObjectBundle\Service\ResolveMediaObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
            Events::onFlush,
            Events::postFlush,
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

        $this->resolveMediaObject->load($entity);
    }

    /**
     * onFlush
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @throws ExceptionInterface
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof MediaObjectInterface) {
                continue;
            }

            if ($this->resolveMediaObject->persist($entity)) {
                $meta = $entityManager->getClassMetadata(\get_class($entity));
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof MediaObjectInterface) {
                continue;
            }

            if ($this->resolveMediaObject->persist($entity, $unitOfWork->getEntityChangeSet($entity))) {
                $meta = $entityManager->getClassMetadata(\get_class($entity));
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    /**
     * postFlush
     *
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                if (!$entity instanceof MediaObjectInterface) {
                    continue;
                }

                $this->resolveMediaObject->load($entity);
            }
        }
    }
}
