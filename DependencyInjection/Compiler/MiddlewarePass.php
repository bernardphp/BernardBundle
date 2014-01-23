<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $factories = array(
            'consumer' => array(),
            'producer' => array(),
        );

        foreach ($container->findTaggedServiceIds('bernard.middleware') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['type']) || !in_array($attrs['type'], array('producer', 'consumer'))) {
                    throw new \RuntimeException(sprintf('Each tag named "bernard.producer" of service "%s" must have at "type" attribute of either "consumer" or "producer".', $id));
                }

                $factories[$attrs['type']][] = new Reference($id);
            }
        }

        $container->getDefinition('bernard.middleware_consumer')
            ->setArguments(array($factories['consumer']));

        $container->getDefinition('bernard.middleware_producer')
            ->setArguments(array($factories['producer']));
    }
}
