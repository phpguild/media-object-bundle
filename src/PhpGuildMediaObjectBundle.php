<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle;

use PhpGuild\MediaObjectBundle\DependencyInjection\PhpGuildMediaObjectExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class PhpGuildMediaObjectBundle
 */
class PhpGuildMediaObjectBundle extends Bundle
{
    /**
     * getContainerExtension
     *
     * @return ExtensionInterface
     */
    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new PhpGuildMediaObjectExtension();
        }

        return parent::getContainerExtension();
    }
}
