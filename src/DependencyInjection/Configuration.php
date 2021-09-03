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
                ->scalarNode('default_filter')
                    ->isRequired()
                ->end()
                ->scalarNode('media_prefix')
                    ->isRequired()
                ->end()
                ->scalarNode('original_prefix')
                    ->isRequired()
                ->end()
                ->scalarNode('cache_prefix')
                    ->isRequired()
                ->end()
                ->scalarNode('resolve_prefix')
                    ->isRequired()
                ->end()
                ->scalarNode('resolve_filter_prefix')
                    ->isRequired()
                ->end()
                ->scalarNode('web_root')
                    ->isRequired()
                ->end()
                ->scalarNode('data_root')
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
