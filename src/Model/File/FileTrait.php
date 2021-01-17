<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Model\File;

// PR: https://github.com/doctrine/annotations/pull/102
use PhpGuild\MediaObjectBundle\Annotation as MediaObject;

/**
 * Trait FileTrait
 */
trait FileTrait
{
    use FilePropertiesTrait;
    use FileMethodsTrait;
}
