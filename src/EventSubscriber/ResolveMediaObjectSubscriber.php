<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\EventSubscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Column;
use Doctrine\Common\EventSubscriber;
use PhpGuild\MediaObjectBundle\Annotation\Uploadable;
use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ResolveMediaObjectSubscriber
 */
final class ResolveMediaObjectSubscriber implements EventSubscriber
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Reader $annotationReader */
    private $annotationReader;

    /** @var PropertyAccessorInterface $propertyAccessor */
    private $propertyAccessor;

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    /**
     * ResolveMediaObjectSubscriber constructor.
     *
     * @param EntityManagerInterface    $entityManager
     * @param Reader                    $annotationReader
     * @param PropertyAccessorInterface $propertyAccessor
     * @param FileUploader              $fileUploader
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Reader $annotationReader,
        PropertyAccessorInterface $propertyAccessor,
        FileUploader $fileUploader,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->annotationReader = $annotationReader;
        $this->propertyAccessor = $propertyAccessor;
        $this->fileUploader = $fileUploader;
        $this->urlGenerator = $urlGenerator;
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
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        /**
         * @var \ReflectionProperty $property
         * @var bool $isCollection
         * @var Uploadable $uploadable
         */
        foreach ($this->getEntityProperies($entity) as [ $property, $isCollection, $uploadable ]) {
            $context = $this->urlGenerator->getContext();
            $value = $this->propertyAccessor->getValue($entity, $property->name);

            if (
                0 !== strncmp('http://', $value, 7)
                && 0 !== strncmp('https://', $value, 8)
            ) {
                $value = $context->getScheme() . '://' . $context->getHost() .
                    ('http' === $context->getScheme() && 80 !== $context->getHttpPort() ? ':' . $context->getHttpPort() : '') .
                    ('https' === $context->getScheme() && 443 !== $context->getHttpsPort() ? ':' . $context->getHttpsPort() : '') .
                    $this->fileUploader->getRelativeFile($value)
                ;
            }

            $this->propertyAccessor->setValue($entity, $uploadable->getUrlProperty(), $value);
        }
    }

    /**
     * prePersist
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        $this->resolveObject($entity);
    }

    /**
     * preUpdate
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObjectInterface) {
            return;
        }

        $this->resolveObject($entity, $args->getEntityChangeSet());
    }

    /**
     * resolveObject
     *
     * @param MediaObjectInterface $entity
     * @param array             $changeSet
     */
    public function resolveObject(MediaObjectInterface $entity, array $changeSet = []): void
    {
        /**
         * @var \ReflectionProperty $property
         * @var bool $isCollection
         * @var Uploadable $uploadable
         */
        foreach ($this->getEntityProperies($entity) as [ $property, $isCollection, $uploadable ]) {
            $value = $this->propertyAccessor->getValue($entity, $property->name);
            $prevValue = $changeSet[$property->name][0] ?? null;

            if ($prevValue && !$value) {
                $this->propertyAccessor->setValue($entity, $property->name, $prevValue);
                continue;
            }

            if (true === $isCollection) {
                if (!\is_array($value)) {
                    continue;
                }

                $files = [];
                foreach ($value as $file) {
                    if (!$file) {
                        continue;
                    }
                    $files[] = $this->prepareUploadFile($file);
                }

                $this->propertyAccessor->setValue($entity, $property->name, $files);

                foreach ($prevValue ?? [] as $prevFile) {
                    if (!$prevFile) {
                        continue;
                    }
                    $this->fileUploader->deleteFile($prevFile);
                }
            } else {
                if (!$value) {
                    continue;
                }

                $this->propertyAccessor->setValue($entity, $property->name, $this->prepareUploadFile($value));

                if ($prevValue) {
                    $this->fileUploader->deleteFile($prevValue);
                }
            }
        }
    }

    /**
     * getEntityProperies
     *
     * @param MediaObjectInterface $entity
     *
     * @return array
     */
    private function getEntityProperies(MediaObjectInterface $entity): array
    {
        $propertyList = [];
        $classMetadata = $this->entityManager->getClassMetadata(\get_class($entity));

        foreach ($classMetadata->getReflectionProperties() as $property) {
            $uploadable = null;
            $isCollection = false;

            foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof Column && 'array' === $annotation->type) {
                    $isCollection = true;
                }

                if (!$annotation instanceof Uploadable) {
                    continue;
                }

                $uploadable = $annotation;
            }

            if (null === $uploadable) {
                continue;
            }

            $propertyList[] = [ $property, $isCollection, $uploadable ];
        }

        return $propertyList;
    }


    /**
     * prepareUploadFile
     *
     * @param mixed $file
     *
     * @return string|null
     */
    protected function prepareUploadFile($file): ?string
    {
        if (\is_string($file) && 0 === strncmp($file, 'data:', 5)) {
            return $this->fileUploader->copyFromBase64($file);
        }

        if ($file instanceof UploadedFile) {
            return $this->fileUploader->copyFromFile($file);
        }

        if ($file instanceof File) {
            return $file->getFilename();
        }

        return $file;
    }
}
