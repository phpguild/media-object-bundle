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
        if (0 !== strncmp((string) $data, 'data:', 5)) {
            return $data;
        }

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

        switch ($match['mimeType']) {
            case 'image/jpg':
                $match['mimeType'] = 'image/jpeg';
                break;
            case 'image/heif':
                $match['mimeType'] = 'image/heic';
                break;
        }

        $filesystem = new Filesystem();
        $tempfile = $filesystem->tempnam('/tmp', 'symfony');
        $filesystem->dumpFile($tempfile, \base64_decode($match['encoded']));

        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tempfile);
        if ($match['mimeType'] !== $mimeType) {
            $filesystem->remove($tempfile);
            throw new NotNormalizableValueException('The MimeType is invalid.');
        }

        $extension = (new MimeTypes())->getExtensions($mimeType)[0] ?? null;
        if (!$extension) {
            $filesystem->remove($tempfile);
            throw new NotNormalizableValueException('The extension is invalid.');
        }

        return new File($tempfile);
    }
}
