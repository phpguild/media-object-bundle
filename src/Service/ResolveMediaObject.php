<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PhpGuild\MediaObjectBundle\Annotation\Uploadable;
use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;
use PhpGuild\MediaObjectBundle\Upload\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class ResolveMediaObject
 */
final class ResolveMediaObject
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var Reader $annotationReader */
    private $annotationReader;

    /** @var PropertyAccessorInterface $propertyAccessor */
    private $propertyAccessor;

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var CacheManager $cacheManager */
    private $cacheManager;

    /**
     * ResolveMediaObject constructor.
     *
     * @param EntityManagerInterface    $entityManager
     * @param Reader                    $annotationReader
     * @param PropertyAccessorInterface $propertyAccessor
     * @param FileUploader              $fileUploader
     * @param CacheManager              $cacheManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Reader $annotationReader,
        PropertyAccessorInterface $propertyAccessor,
        FileUploader $fileUploader,
        CacheManager $cacheManager
    ) {
        $this->entityManager = $entityManager;
        $this->annotationReader = $annotationReader;
        $this->propertyAccessor = $propertyAccessor;
        $this->fileUploader = $fileUploader;
        $this->cacheManager = $cacheManager;
    }

    /**
     * load
     *
     * @param MediaObjectInterface $entity
     */
    public function load(MediaObjectInterface $entity): void
    {
        /**
         * @var \ReflectionProperty $property
         * @var bool $isCollection
         * @var Uploadable $uploadable
         */
        foreach ($this->getEntityProperies(\get_class($entity)) as [ $property, $isCollection, $uploadable ]) {
            $file = $this->propertyAccessor->getValue($entity, $property->name);
            if (!$file) {
                continue;
            }

            if (!$file instanceof File) {
                try {
                    $file = new File($this->fileUploader->getAbsoluteFile($file));
                } catch (FileNotFoundException $exception) {
                    $file = null;
                }

                $this->propertyAccessor->setValue($entity, $property->name, $file);
            }

            $url = null;
            if ($file instanceof File) {
                $url = $this->cacheManager->getBrowserPath(
                    $this->fileUploader->getChunkedFileName($file->getFilename()) . '/' . $file->getFilename(),
                    $uploadable->getFilter()
                );
            }

            $this->propertyAccessor->setValue($entity, $uploadable->getUrlProperty(), $url);
        }
    }

    /**
     * resolve
     *
     * @param MediaObjectInterface $entity
     *
     * @throws ExceptionInterface
     */
    public function resolve(MediaObjectInterface $entity): void
    {
        /**
         * @var \ReflectionProperty $property
         * @var bool $isCollection
         * @var Uploadable $uploadable
         */
        foreach ($this->getEntityProperies(\get_class($entity)) as [ $property, $isCollection, $uploadable ]) {
            $value = $this->propertyAccessor->getValue($entity, $property->name);
            if (null === $value) {
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
                    $files[] = $this->fileUploader->prepare($file);
                }

                $this->propertyAccessor->setValue($entity, $property->name, $files);
                continue;
            }

            $this->propertyAccessor->setValue($entity, $property->name, $this->fileUploader->prepare($value));
        }
    }

    /**
     * persist
     *
     * @param MediaObjectInterface $entity
     * @param array                $changeSet
     *
     * @return bool
     *
     * @throws ExceptionInterface
     */
    public function persist(MediaObjectInterface $entity, array $changeSet = []): bool
    {
        $recompute = false;

        $this->resolve($entity);

        /**
         * @var \ReflectionProperty $property
         * @var bool $isCollection
         * @var Uploadable $uploadable
         */
        foreach ($this->getEntityProperies(\get_class($entity)) as [ $property, $isCollection, $uploadable ]) {
            $prevFilename = $changeSet[$property->name][0] ?? null;
            $value = $this->propertyAccessor->getValue($entity, $property->name);

            if (true === $isCollection) {
                if (!\is_array($value)) {
                    continue;
                }

                $files = [];
                /** @var File $file */
                foreach ($value as $file) {
                    if (!$file instanceof File) {
                        continue;
                    }
                    $files[] = $this->fileUploader->copy($file);
                }

                $this->propertyAccessor->setValue($entity, $property->name, $files);
                $recompute = true;

                foreach ($prevFilename ?? [] as $prevFile) {
                    if (!$prevFile) {
                        continue;
                    }
                    $this->fileUploader->delete($prevFile);
                }
                continue;
            }

            if (!$value instanceof File) {
                $this->propertyAccessor->setValue($entity, $property->name, $prevFilename);
                $recompute = true;
                continue;
            }

            $filename = $value->getFilename();
            if ($filename === $prevFilename) {
                $this->propertyAccessor->setValue($entity, $property->name, $filename);
                $recompute = true;
                continue;
            }

            $this->propertyAccessor->setValue($entity, $property->name, $this->fileUploader->copy($value));
            $recompute = true;

            if ($prevFilename) {
                $this->fileUploader->delete($prevFilename);
            }
        }

        return $recompute;
    }

    /**
     * getMediaCollection
     *
     * @return array
     */
    public function getMediaCollection(): array
    {
        $mediaCollection = [];
        $metadataCollection = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $alias = 'a';

        foreach ($metadataCollection as $metadata) {
            $className = $metadata->getName();
            $properties = $this->getEntityProperies($className);
            if (!\count($properties)) {
                continue;
            }

            $fields = [];
            foreach ($properties as [ $property, $isCollection, $uploadable ]) {
                $fields[] = $property->getName();
            }

            $select = implode(', ', array_map(static function (string $field) use ($alias) {
                return sprintf('%s.%s', $alias, $field);
            }, $fields));

            $where = implode(' OR ', array_map(static function (string $field) use ($alias) {
                return sprintf('%s.%s IS NOT NULL', $alias, $field);
            }, $fields));

            $items = $this->entityManager->createQueryBuilder()
                ->select($select)
                ->from($className, $alias)
                ->where($where)
                ->getQuery()
                ->getResult()
            ;

            foreach ($items as $item) {
                foreach ($item as $value) {
                    if (!$value) {
                        continue;
                    }
                    $mediaCollection[] = $this->fileUploader->getAbsoluteFile($value);
                }
            }
        }

        return $mediaCollection;
    }

    /**
     * getEntityProperies
     *
     * @param string $className
     *
     * @return array
     */
    private function getEntityProperies(string $className): array
    {
        $propertyList = [];
        $classMetadata = $this->entityManager->getClassMetadata($className);

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
}
