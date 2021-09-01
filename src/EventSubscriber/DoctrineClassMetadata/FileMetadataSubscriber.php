<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\EventSubscriber\DoctrineClassMetadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\MappingException;
use PhpGuild\MediaObjectBundle\Model\File\FileInterface;

/**
 * Class FileMetadataSubscriber
 */
final class FileMetadataSubscriber implements EventSubscriber
{
    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [ Events::loadClassMetadata ];
    }

    /**
     * loadClassMetadata
     *
     * @param LoadClassMetadataEventArgs $loadClassMetadataEventArgs
     *
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (
            true === $classMetadata->isMappedSuperclass
            || null === $classMetadata->reflClass
            || !is_a($classMetadata->reflClass->getName(), FileInterface::class, true)
        ) {
            return;
        }

        $classMetadata->mapField([
            'nullable' => true,
            'type' => Types::STRING,
            'fieldName' => FileInterface::FILE_COLUMN_NAME,
            'columnName' => FileInterface::FILE_COLUMN_NAME,
        ]);
    }
}
