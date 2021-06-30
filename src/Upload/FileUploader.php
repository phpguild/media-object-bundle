<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Upload;

use PhpGuild\MediaObjectBundle\Serializer\Base64DataUriNormalizer;
use PhpGuild\MediaObjectBundle\Serializer\HttpUriNormalizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class FileUploader
 */
class FileUploader
{
    /** @var string $publicPath */
    private $publicPath;

    /** @var string $mediaOriginalDirectory */
    private $mediaOriginalDirectory;

    /**
     * FileUploader constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $configuration = $parameterBag->get('phpguild_media_object');
        $this->publicPath = $configuration['public_path'];
        $this->mediaOriginalDirectory = $configuration['media_original_directory'];
    }

    /**
     * prepareUploadFile
     *
     * @param mixed $file
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
        }

        return null;
    }

    /**
     * copy
     *
     * @param File $file
     *
     * @return string|null
     */
    public function copy(File $file): ?string
    {
        if (!$file) {
            return null;
        }

        $fileName = $this->generateRandomFileName() . '.' . $file->guessExtension();
        $file->move($this->getAbsolutePath($fileName), $fileName);

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
        if (!preg_match('/^[[:xdigit:]]{40}\./', $fileName)) {
            return '';
        }

        return rtrim(chunk_split(substr($fileName, 0, 6), 1, '/'), '/');
    }
}
