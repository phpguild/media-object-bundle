<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * getConfigTreeBuilder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('phpguild_media_object');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('public_path')->isRequired()->end()
                ->scalarNode('media_original_directory')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
