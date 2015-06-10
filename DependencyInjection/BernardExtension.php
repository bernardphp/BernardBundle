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

            case 'sqs':
                $this->registerSqsConfiguration($config['options'], $container);
                break;
        }

        $this->registerListeners($config['listeners'], $container);
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

    private function registerFlatFileConfiguration(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.file')->replaceArgument(0, $config['directory']);
    }

    private function registerPhpRedisConfiguration(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.phpredis')->replaceArgument(0, new Reference($config['phpredis_service']));
    }

    private function registerPredisConfiguration(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.predis')->replaceArgument(0, new Reference($config['predis_service']));
    }

    private function registerIronMQConfiguration(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.ironmq')->replaceArgument(0, new Reference($config['ironmq_service']));
    }

    private function registerSqsConfiguration(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('bernard.driver.sqs')->replaceArgument(0, new Reference($config['sqs_service']))
                                                       ->replaceArgument(1, $config['sqs_queue_map'])
                                                       ->replaceArgument(2, $config['prefetch']);
    }

    private function registerListeners(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $params) {
            if (empty($params)) {
                continue;
            }

            if (is_array($params) && !$params['enabled']) {
                continue;
            }

            // Enable listener.
            $listener = $container->getDefinition('bernard.listener.'.$id);
            $listener->addTag('kernel.event_subscriber');

            if ($id === 'logger') {
                $listener->replaceArgument(0, new Reference($params['service']));
            } elseif ($id === 'failure') {
                $listener->replaceArgument(1, $params['queue_name']);
            }
        }
    }
}
