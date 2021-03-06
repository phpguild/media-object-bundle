<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Model\File;

use PhpGuild\MediaObjectBundle\Annotation as MediaObject;

/**
 * Trait FilePropertiesTrait
 */
trait FilePropertiesTrait
{
    /**
     * @var string|null
     * @MediaObject\Uploadable(urlProperty="url")
     */
    protected $file;

    /**
     * @var string|null
     */
    protected $url;
}
