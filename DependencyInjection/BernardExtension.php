<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class BernardExtension extends \Symfony\Component\HttpKernel\DependencyInjection\Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        // Theese can be used by the different drivers such as sqs or flat file driver.
        $container->setParameter('bernard.options.prefetch', $config['prefetch']);
        $container->setParameter('bernard.options.queue_map', $config['queue_map']);

        $container->getDefinition('bernard.driver.' . $config['driver'])
            ->setAlias('bernard.driver');

        $container->getDefinition('bernard.serializer.' . $config['driver'])
            ->setAlias('bernard.serializer');
    }
}
