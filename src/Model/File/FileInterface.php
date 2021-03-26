<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Model\File;

use PhpGuild\MediaObjectBundle\Model\MediaObjectInterface;

/**
 * Interface FileInterface
 */
interface FileInterface extends MediaObjectInterface
{
    public const COLUMN_NAME = 'file';

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
     * getUrl
     *
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * setUrl
     *
     * @param string|null $url
     *
     * @return FileInterface
     */
    public function setUrl(?string $url): FileInterface;
}
