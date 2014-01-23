<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('bernard_bernard');

        $root
            ->children()
                ->scalarNode('driver')->isRequired()->end()
                ->scalarNode('serializer')->defaultValue('simple')->isRequired()->end()
                ->scalarNode('prefetch')->defaultNull()->end()
                ->scalarNode('directory')->defaultValue('')->end()
                ->arrayNode('queue_map')->defaultValue(array())->end()
            ->end()
        ;

        return $tree;
    }
}
