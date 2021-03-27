<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Bridge\ApiPlatform\Serializer;

use ApiPlatform\Core\Serializer\ItemNormalizer;
use PhpGuild\MediaObjectBundle\Model\File\FileInterface;
use PhpGuild\MediaObjectBundle\Service\ResolveMediaObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Class FileItemNormalizer
 */
class FileItemNormalizer implements DenormalizerInterface
{
    /** @var ItemNormalizer $normalizer */
    private $normalizer;

    /** @var ResolveMediaObject $resolveMediaObject */
    private $resolveMediaObject;

    /**
     * FileNormalizer constructor.
     *
     * @param ItemNormalizer     $itemNormalizer
     * @param ResolveMediaObject $resolveMediaObject
     */
    public function __construct(
        ItemNormalizer $itemNormalizer,
        ResolveMediaObject $resolveMediaObject
    ) {
        $this->normalizer = $itemNormalizer;
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
     * @inheritDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return \is_a($type, FileInterface::class, true);
    }
}
