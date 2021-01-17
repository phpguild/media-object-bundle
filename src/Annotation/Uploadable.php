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
    /** @var string $default */
    private $default = 'default.png';

    /** @var string $urlProperty */
    private $urlProperty;

    /**
     * Uploadable constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->default = $data['default'] ?? $this->default;
        $this->urlProperty = $data['urlProperty'] ?? $this->urlProperty;
    }

    /**
     * getDefault
     *
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default;
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
