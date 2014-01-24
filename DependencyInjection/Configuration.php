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

        $drivers = array('file', 'predis', 'doctrine');
        $serializers = array('simple', 'jms', 'symfony');

        $root
            ->validate()
                ->ifTrue(function ($v) { return 'file' === $v['driver'] && empty($v['options']['directory']); })
                ->thenInvalid('The "directory" option must be defined when using the file driver.')
            ->end()
            ->children()
                ->scalarNode('driver')
                    ->validate()
                        ->ifNotInArray($drivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($drivers))
                    ->end()
                ->end()
                ->scalarNode('serializer')
                    ->defaultValue('simple')
                    ->validate()
                        ->ifNotInArray($serializers)
                        ->thenInvalid('The serializer %s is not supported. Please choose of of ' . json_encode($serializers))
                    ->end()
                ->end()
                ->arrayNode('consumer_middlewares')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('error_log')->defaultFalse()->end()
                        ->booleanNode('logger')->defaultFalse()->end()
                        ->booleanNode('failure')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('producer_middlewares')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('logger')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('prefetch')->defaultNull()->end()
                        ->scalarNode('directory')->defaultValue('')->end()
                        ->scalarNode('connection')->defaultValue('default')->end()
                        ->arrayNode('queue_map')->prototype('array')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }
}
