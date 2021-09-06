<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Annotation;

/**
 * Class Uploadable
 *
 * @Annotation
 * @Target({ "PROPERTY", "ANNOTATION" })
 */
class Uploadable
{
    /** @var string|null $filter */
    private $filter;

    /** @var string|null $urlProperty */
    private $urlProperty;

    /**
     * Uploadable constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->filter = $data['filter'] ?? null;
        $this->urlProperty = $data['urlProperty'] ?? null;
    }

    /**
     * getFilter
     *
     * @param string|null $default
     *
     * @return string|null
     */
    public function getFilter(?string $default = null): ?string
    {
        return $this->filter ?: $default;
    }

    /**
     * getUrlProperty
     *
     * @return string|null
     */
    public function getUrlProperty(): ?string
    {
        return $this->urlProperty;
    }
}
