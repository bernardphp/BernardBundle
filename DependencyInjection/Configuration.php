<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('bernard');

        $root
            ->children()
                ->enumNode('driver')
                    ->isRequired()
                    ->values(['doctrine', 'file', 'phpredis', 'predis', 'ironmq'])
                ->end()

                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('connection')->defaultValue('default')->end()
                        ->scalarNode('directory')->defaultNull()->end()
                        ->scalarNode('phpredis_service')->defaultValue('snc_redis.bernard')->end()
                        ->scalarNode('predis_service')->defaultValue('snc_redis.bernard')->end()
                        ->scalarNode('ironmq_service')->defaultNull()->end()
                    ->end()
                ->end()

                ->arrayNode('listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('error_log')->defaultFalse()->end()
                        ->arrayNode('logger')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('service')->defaultValue('logger')->end()
                            ->end()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return ['enabled' => true, 'service' => $v]; })
                            ->end()
                        ->end()
                        ->arrayNode('failure')
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('queue_name')->defaultValue('failed')->end()
                            ->end()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return ['enabled' => true, 'queue_name' => $v]; })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $this
            ->validateDriver($root, 'file', 'directory')
            ->validateDriver($root, 'phpredis', 'phpredis_service')
            ->validateDriver($root, 'predis', 'predis_service')
            ->validateDriver($root, 'ironmq', 'ironmq_service')
        ;

        return $tree;
    }

    /**
     * @param NodeDefinition $node
     * @param string         $driver
     * @param string         $option
     *
     * @return self
     */
    private function validateDriver(NodeDefinition $node, $driver, $option)
    {
        $node
            ->validate()
                ->ifTrue(function ($v) use ($driver, $option) {
                    return $driver === $v['driver'] && empty($v['options'][$option]);
                })
                ->thenInvalid(sprintf('The "%s" option must be defined when using the "%s" driver.', $option, $driver))
            ->end()
        ;

        return $this;
    }
}
