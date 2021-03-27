<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Serializer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Unirest\Request;

/**
 * Class HttpUriNormalizer
 */
class HttpUriNormalizer implements DenormalizerInterface
{
    private const SUPPORTED_TYPES = [
        \SplFileInfo::class => true,
        \SplFileObject::class => true,
        File::class => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (
            0 !== strncmp((string) $data, 'https:', 6)
            && 0 !== strncmp((string) $data, 'http:', 5)
        ) {
            return $data;
        }

        $response = Request::get($data);

        if (200 !== $response->code) {
            throw new NotNormalizableValueException(
                sprintf('The response code %s of URL %s is invalid.', $response->code, $data)
            );
        }

        $filesystem = new Filesystem();
        $tempfile = $filesystem->tempnam('/tmp', 'symfony');
        $filesystem->dumpFile($tempfile, $response->raw_body);

        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tempfile);
        $extension = (new MimeTypes())->getExtensions($mimeType)[0] ?? null;
        if (!$extension) {
            $filesystem->remove($tempfile);
            throw new NotNormalizableValueException('The extension is invalid.');
        }

        return new File($tempfile);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return isset(self::SUPPORTED_TYPES[$type]);
    }
}
