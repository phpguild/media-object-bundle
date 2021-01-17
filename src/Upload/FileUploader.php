<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Upload;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;

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
     * copyFromBase64
     *
     * @param string|null $file
     *
     * @return string|null
     */
    public function copyFromBase64(?string $file): ?string
    {
        if (!$file) {
            return null;
        }

        [ $mimeType, $data ] = explode(',', $file, 2);
        [ $mimeType, $dataType ] = explode(';', $mimeType, 2);
        [ $dataPrefix, $mimeType ] = explode(':', $mimeType, 2);

        if ('base64' !== $dataType) {
            return null;
        }

        $data = base64_decode($data);
        $mimeType = str_replace([ 'image/jpg' ], [ 'image/jpeg' ], $mimeType);

        $extension = (new MimeTypes())->getExtensions($mimeType)[0] ?? null;
        if (!$extension) {
            return null;
        }

        $fileName = $this->generateRandomFileName() . '.' . $extension;
        $filePath = $this->getAbsoluteFile($fileName);
        $directory = \dirname($filePath);

        if (!\is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($filePath, $data);

        return $fileName;
    }

    /**
     * copyFromFile
     *
     * @param File|null $file
     *
     * @return string|null
     */
    public function copyFromFile(?File $file): ?string
    {
        if (!$file) {
            return null;
        }

        $fileName = $this->generateRandomFileName() . '.' . $file->guessExtension();
        $file->move(\dirname($this->getAbsolutePath($fileName)), $fileName);
        
        return $fileName;
    }

    /**
     * deleteFile
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function deleteFile(string $fileName): bool
    {
        $file = $this->getAbsolutePath($fileName);

        return file_exists($file) && unlink($file);
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
     * generateRandomFileName
     *
     * @return string
     */
    private function generateRandomFileName(): string
    {
        return hash('sha1', uniqid((string) microtime(true), true));
    }

    /**
     * getChunkedFileName
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getChunkedFileName(string $fileName): string
    {
        return ltrim($this->getChunkedDirectoryPart($fileName), '/');
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
