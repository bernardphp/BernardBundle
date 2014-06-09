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
            ->validate()
                ->ifTrue(function ($v) { return 'file' === $v['driver'] && empty($v['options']['directory']); })
                ->thenInvalid('The "directory" option must be defined when using the file driver.')
            ->end()
            ->children()
                ->enumNode('driver')
                    ->values(array('file', 'predis', 'doctrine'))
                ->end()
                ->enumNode('serializer')
                    ->defaultValue('simple')
                    ->values(array('jms', 'simple', 'symfony'))
                ->end()
                ->arrayNode('middlewares')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('error_log')->defaultFalse()->end()
                        ->booleanNode('logger')->defaultFalse()->end()
                        ->booleanNode('failures')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('prefetch')->defaultNull()->end()
                        ->scalarNode('directory')->defaultValue('')->end()
                        ->scalarNode('connection')->defaultValue('default')->end()
                        ->scalarNode('phpredis_service')->defaultValue('snc_redis.bernard')->end()
                        ->arrayNode('queue_map')->prototype('array')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }
}
