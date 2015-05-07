<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $receivers = [];

        foreach ($container->findTaggedServiceIds('bernard.receiver') as $id => $tags) {
            foreach ($tags as $attrs) {
                if (!isset($attrs['message'])) {
                    throw new \RuntimeException(sprintf('Each tag named "bernard.receiver" of service "%s" must have at "message" attribute that species the message name it is associated with.', $id));
                }

                $receivers[$attrs['message']] = $id;
            }
        }

        $container->getDefinition('bernard.router')->replaceArgument(1, $receivers);
    }
}
