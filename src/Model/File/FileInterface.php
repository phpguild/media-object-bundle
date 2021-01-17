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
     * @return string|null
     */
    public function getFile(): ?string;

    /**
     * setFile
     *
     * @param string|null $file
     *
     * @return FileInterface|self
     */
    public function setFile(?string $file): FileInterface;

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
     * @return FileInterface|self
     */
    public function setUrl(?string $url): FileInterface;
}
