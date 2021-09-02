<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Upload;

use PhpGuild\MediaObjectBundle\Serializer\Base64DataUriNormalizer;
use PhpGuild\MediaObjectBundle\Serializer\HttpUriNormalizer;
use PhpGuild\MediaObjectBundle\Service\ResolveCache;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class FileUploader.
 */
class FileUploader
{
    /** @var ResolveCache $resolveCache */
    private $resolveCache;

    /** @var string|null $publicPath */
    private $publicPath;

    /** @var string|null $mediaOriginalDirectory */
    private $mediaOriginalDirectory;

    /**
     * FileUploader constructor.
     *
     * @param ResolveCache          $resolveCache
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ResolveCache $resolveCache,
        ParameterBagInterface $parameterBag
    ) {
        $this->resolveCache = $resolveCache;

        $configuration = $parameterBag->get('phpguild_media_object');
        $this->publicPath = $configuration['public_path'] ?? null;
        $this->mediaOriginalDirectory = $configuration['media_original_directory'] ?? null;
    }

    /**
     * prepare
     *
     * @param $file
     *
     * @return File|null
     *
     * @throws ExceptionInterface
     */
    public function prepare($file): ?File
    {
        if ($file) {
            if ($file instanceof File) {
                return $file;
            }

            $file = (new Base64DataUriNormalizer())->denormalize($file, File::class);
            if ($file instanceof File) {
                return $file;
            }

            $file = (new HttpUriNormalizer())->denormalize($file, File::class);
            if ($file instanceof File) {
                return $file;
            }

            if ($this->isValidFilename($file) && $this->existsFilename($file)) {
                return new File($this->getAbsoluteFile($file));
            }
        }

        return null;
    }

    /**
     * copy
     *
     * @param File $file
     * @param bool $resolveCache
     *
     * @return string|null
     */
    public function copy(File $file, bool $resolveCache = false): ?string
    {
        if (!$file) {
            return null;
        }

        $fileName = $this->generateRandomFileName() . '.' . $file->guessExtension();
        $file->move($this->getAbsolutePath($fileName), $fileName);

        if ($resolveCache) {
            $image = sprintf('%s/%s', $this->getChunkedFileName($fileName), $fileName);
            $this->resolveCache->resolve($image);
        }

        return $fileName;
    }

    /**
     * delete
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function delete(string $fileName): bool
    {
        $file = $this->getAbsolutePath($fileName);

        return file_exists($file) && is_file($file) && unlink($file);
    }

    /**
     * getAbsoluteFile
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getAbsoluteFile(string $fileName): string
    {
        return $this->getAbsolutePath($fileName) . '/' . $fileName;
    }

    /**
     * getRelativeFile
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getRelativeFile(string $fileName): string
    {
        return $this->getRelativePath($fileName) . '/' . $fileName;
    }

    /**
     * getAbsolutePath
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getAbsolutePath(string $fileName): string
    {
        return $this->publicPath . $this->getRelativePath($fileName);
    }

    /**
     * getRelativePath
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getRelativePath(string $fileName): string
    {
        return '/' . $this->mediaOriginalDirectory . '/' . $this->getChunkedFileName($fileName);
    }

    /**
     * getChunkedFileName
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getChunkedFileName(string $fileName): string
    {
        return ltrim($this->getChunkedDirectoryPart($fileName), '/');
    }

    /**
     * getMediaCollection
     *
     * @param int $modifiedTime
     *
     * @return array
     */
    public function getMediaCollection(int $modifiedTime = 3600 * 24): array
    {
        $iterator = new \RecursiveDirectoryIterator(
            sprintf('%s/%s', $this->publicPath, $this->mediaOriginalDirectory)
        );

        $mediaCollection = [];

        foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST) as $file) {
            if (!$file->isFile() || $file->getMTime() > time() - $modifiedTime) {
                continue;
            }
            $mediaCollection[] = (string) $file;
        }

        return $mediaCollection;
    }

    /**
     * generateRandomFileName
     *
     * @return string
     */
    private function generateRandomFileName(): string
    {
        return hash('sha1', uniqid((string) microtime(true), true));
    }

    /**
     * getChunkedDirectoryPart
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getChunkedDirectoryPart(string $fileName): string
    {
        if (!$this->isValidFilename($fileName)) {
            return '';
        }

        return rtrim(chunk_split(substr($fileName, 0, 6), 1, '/'), '/');
    }

    /**
     * isValidFilename
     *
     * @param mixed $fileName
     *
     * @return bool
     */
    private function isValidFilename($fileName): bool
    {
        return (\is_string($fileName) && preg_match('/^[[:xdigit:]]{40}\.[[:alnum:]]+$/', $fileName));
    }

    /**
     * existsFilename
     *
     * @param string $fileName
     *
     * @return bool
     */
    private function existsFilename(string $fileName): bool
    {
        return file_exists($this->getAbsoluteFile($fileName));
    }
}
