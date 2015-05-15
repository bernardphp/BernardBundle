<?php

namespace Bernard\BernardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NormalizerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $normalizers = array_map(function ($id) {
            return new Reference($id);
        }, array_keys($container->findTaggedServiceIds('bernard.normalizer')));

        $container->getDefinition('bernard.normalizer')->replaceArgument(0, $normalizers);
    }
}
