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
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * setFile
     *
     * @param string|null $file
     *
     * @return FileInterface|self
     */
    public function setFile(?string $file): FileInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * getUrl
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * setUrl
     *
     * @param string|null $url
     *
     * @return FileInterface|self
     */
    public function setUrl(?string $url): FileInterface
    {
        $this->url = $url;

        return $this;
    }
}
