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
