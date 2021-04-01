<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Model\File;

use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;

/**
 * Interface FileInterface
 */
interface FileInterface extends MediaObjectInterface
{
    public const FILE_COLUMN_NAME = 'file';

    /**
     * getFile
     *
     * @return mixed|null
     */
    public function getFile();

    /**
     * setFile
     *
     * @param mixed|null $file
     *
     * @return FileInterface
     */
    public function setFile($file): FileInterface;

    /**
     * getFileUrl
     *
     * @return string|null
     */
    public function getFileUrl(): ?string;

    /**
     * setFileUrl
     *
     * @param string|null $fileUrl
     *
     * @return FileInterface
     */
    public function setFileUrl(?string $fileUrl): FileInterface;
}
