<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Serializer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;

/**
 * Class Base64DataUriNormalizer
 */
class Base64DataUriNormalizer extends DataUriNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        parent::denormalize($data, $type, $format, $context);

        $match = [];
        # Symfony's DataUriNormalizer::denormalize already validated the pattern
        preg_match(
            '/^data:(?P<mimeType>[a-z0-9][a-z0-9\!\#\$\&\-\^\_\+\.]{0,126}\/[a-z0-9][a-z0-9\!\#\$\&\-\^\_\+\.]{0,126}' .
            '(;[a-z0-9\-]+\=[a-z0-9\-]+)?)?;base64,(?P<encoded>.+)$/i',
            $data,
            $match
        );

        if (!\array_key_exists('mimeType', $match)) {
            throw new NotNormalizableValueException('The MimeType should be specified in the URI.');
        }

        if ('image/jpg' === $match['mimeType']) {
            $match['mimeType'] = 'image/jpeg';
        }

        $extension = (new MimeTypes())->getExtensions($match['mimeType'])[0] ?? null;
        if (!$extension) {
            throw new NotNormalizableValueException('The extension is invalid.');
        }

        $filesystem = new Filesystem();
        $tempfile = sprintf('%s.%s', $filesystem->tempnam('/tmp', 'symfony'), $extension);
        $filesystem->dumpFile($tempfile, \base64_decode($match['encoded']));

        return new File($tempfile);
    }
}
