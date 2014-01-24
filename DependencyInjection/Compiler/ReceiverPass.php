<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReceiverPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $receivers = array();

        foreach ($container->findTaggedServiceIds('bernard.receiver') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['name'])) {
                    throw new \RuntimeException(sprintf('Each tag named "bernard.receiver" of service "%s" must have at "name" attribute that species the message name it is associated with.', $id));
                }

                $receivers[$attrs['name']] = $id;
            }
        }

        $container->getDefinition('bernard.router')
            ->setArguments(array(new Reference('service_container'), $receivers));
    }
}
