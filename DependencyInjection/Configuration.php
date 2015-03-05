<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('bernard_bernard');

        $this->addNodes($root);
        $this->addValidationRules($root);

        return $tree;
    }

    protected function addNodes(NodeDefinition $root)
    {
        $root
            ->children()
                ->enumNode('driver')
                    ->values(array('file', 'prefis', 'doctrine', 'sqs'))
                    ->isRequired()
                    ->cannotBeEmpty()
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
                ->arrayNode('sqs')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('region')->defaultNull()->end()
                        ->scalarNode('key')->defaultNull()->end()
                        ->scalarNode('secret')->defaultNull()->end()
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
    }

    protected function addValidationRules(NodeDefinition $root)
    {
        $root
            ->validate()
                ->ifTrue(function ($v) { return 'file' === $v['driver'] && empty($v['options']['directory']); })
                ->thenInvalid('The "directory" option must be defined when using the file driver.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return 'sqs' === $v['driver'] && empty($v['sqs']['region']); })
                ->thenInvalid('The "region" option must be defined when using the sqs driver.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return 'sqs' === $v['driver'] && empty($v['sqs']['key']); })
                ->thenInvalid('The "key" option must be defined when using the sqs driver.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return 'sqs' === $v['driver'] && empty($v['sqs']['secret']); })
                ->thenInvalid('The "secret" option must be defined when using the sqs driver.')
            ->end();
    }
}
