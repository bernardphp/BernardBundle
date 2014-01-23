<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definitions = array(
            'consumer' => $container->getDefinition('bernard.middleware_consumer'),
            'producer' => $container->getDefinition('bernard.middleware_producer'),
        );

        foreach ($container->findTaggedServiceIds('bernard.middleware_factory') as $id => $tags) {
            if (!isset($tags[0]['type']) || !in_array($tags[0]['type'], array('producer', 'consumer'))) {
                throw new \RuntimeException(sprintf('Each tag named "bernard.producer" of service "%s" must have at "type" attribute of either "consumer" or "producer".', $id));
            }

            $definitions[$tags[0]['type']]->addMethodCall('push', array(new Reference($id)));
        }
    }
}
