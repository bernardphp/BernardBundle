<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class BernardExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setAlias('bernard.driver', 'bernard.driver.'.$config['driver']);

        switch ($config['driver']) {
            case 'doctrine':
                $this->registerDoctrineConfiguration($config['options'], $container);
                break;

            case 'file':
                $this->registerFlatFileConfiguration($config['options'], $container);
                break;

            case 'phpredis':
                $this->registerPhpRedisConfiguration($config['options'], $container);
                break;

            case 'predis':
                $this->registerPredisConfiguration($config['options'], $container);
                break;

            case 'ironmq':
                $this->registerIronMQConfiguration($config['options'], $container);
                break;
        }
    }

    private function registerDoctrineConfiguration($config, ContainerBuilder $container)
    {
        $container->setAlias('bernard.dbal.connection', 'doctrine.dbal.'.$config['connection'].'_connection');
        $container
            ->getDefinition('bernard.listener.doctrine_schema')
            ->addTag('doctrine.event_listener', [
                'lazy'       => true,
                'event'      => 'postGenerateSchema',
                'connection' => $config['connection']
            ]
        );
    }

    private function registerFlatFileConfiguration($config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.file')->replaceArgument(0, $config['directory']);
    }

    private function registerPhpRedisConfiguration($config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.phpredis')->replaceArgument(0, new Reference($config['phpredis_service']));
    }

    private function registerPredisConfiguration($config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.predis')->replaceArgument(0, new Reference($config['predis_service']));
    }

    private function registerIronMQConfiguration($config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.ironmq')->replaceArgument(0, new Reference($config['ironmq_service']));
    }
}
