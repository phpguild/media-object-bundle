<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Model\File;

/**
 * Trait FileMethodsTrait
 */
trait FileMethodsTrait
{
    /**
     * getFile
     *
     * @return mixed|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * setFile
     *
     * @param mixed|null $file
     *
     * @return FileInterface|self
     */
    public function setFile($file): FileInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * getFileUrl
     *
     * @return string|null
     */
    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    /**
     * setFileUrl
     *
     * @param string|null $fileUrl
     *
     * @return FileInterface
     */
    public function setFileUrl(?string $fileUrl): FileInterface
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }
}
