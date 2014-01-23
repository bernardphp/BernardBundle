<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $router = $container->getDefinition('bernard.router');

        foreach ($container->findTaggedServiceIds('bernard.receiver') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['name'])) {
                    throw new \RuntimeException(sprintf('Each tag named "bernard.receiver" of service "%s" must have at "name" attribute that species the message name it is associated with.', $id));
                }

                $router->addMethodCall('add', array($attrs['name'], $id));
            }
        }
    }
}
