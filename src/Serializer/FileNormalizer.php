<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Serializer;

use PhpGuild\MediaObjectBundle\Model\File\FileInterface;
use PhpGuild\MediaObjectBundle\Service\ResolveMediaObject;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class FileNormalizer
 */
class FileNormalizer implements ContextAwareDenormalizerInterface
{
    /** @var ObjectNormalizer $normalizer */
    private $normalizer;

    /** @var ResolveMediaObject $resolveMediaObject */
    private $resolveMediaObject;

    /**
     * FileNormalizer constructor.
     *
     * @param ObjectNormalizer   $normalizer
     * @param ResolveMediaObject $resolveMediaObject
     */
    public function __construct(
        ObjectNormalizer $normalizer,
        ResolveMediaObject $resolveMediaObject
    ) {
        $this->normalizer = $normalizer;
        $this->resolveMediaObject = $resolveMediaObject;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        /** @var FileInterface $object */
        $object = $this->normalizer->denormalize($data, $type, $format, $context);

        $this->resolveMediaObject->resolve($object);

        return $object;
    }

    /**
     * supportsDenormalization
     *
     * @param mixed       $data
     * @param string      $type
     * @param string|null $format
     * @param array       $context
     *
     * @return bool
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return \is_a($type, FileInterface::class, true);
    }
}
