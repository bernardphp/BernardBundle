<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class BernardBernardExtension extends \Symfony\Component\HttpKernel\DependencyInjection\Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setAlias('bernard.driver', 'bernard.driver.' . $config['driver']);
        $container->setAlias('bernard.serializer', 'bernard.serializer.' . $config['serializer']);

        if ($config['driver'] == 'doctrine') {
            $this->registerDoctrineConfiguration($config['options'], $container);
        }

        if ($config['driver'] == 'file') {
            $this->registerFlatFileConfiguration($config['options'], $container);
        }

        if ($config['driver'] == 'phpredis' && isset($config['options']['phpredis_service']) && $config['options']['phpredis_service']) {
            $this->registerPhpRedisConfiguration($config['options'], $container);
        }

        if ($config['driver'] == 'ironmq') {
            $this->registerIronMQConfiguration($config['options'], $container);
        }

        if ($config['serializer'] == 'jms') {
            $this->registerJmsConfiguration($container);
        }

        if ($config['driver'] == 'sqs') {
            $this->registerSqsConfiguration($config, $container);
        }

        $this->registerMiddlewaresConfiguration($config['middlewares'], $container);
    }

    protected function registerFlatFileConfiguration($config, $container)
    {
        $container->getDefinition('bernard.driver.file')->replaceArgument(0, $config['directory']);
    }

    protected function registerSqsConfiguration(array $config, ContainerBuilder $container)
    {
        $sqsClientDefinition = new Definition();
        $sqsClientDefinition->setClass('Aws\Sqs\SqsClient')
                            ->setFactory('Aws\Sqs\SqsClient::factory')
                            ->setArguments(
                                array(
                                    array(
                                        'region' => $config['sqs']['region'],
                                        'key' => $config['sqs']['key'],
                                        'secret' => $config['sqs']['secret'],
                                    )
                                )
                            );
        $container->getDefinition('bernard.driver.sqs')->replaceArgument(0, $sqsClientDefinition);

        $container->getDefinition('bernard.driver.sqs')->replaceArgument(1, $config['options']['queue_map']);
        $container->getDefinition('bernard.driver.sqs')->replaceArgument(2, $config['options']['prefetch']);
    }

    protected function registerDoctrineConfiguration($config, $container)
    {
        $container->getDefinition('bernard.schema_listener')
            ->addTag('doctrine.event_listener', array('lazy' => true, 'connection' => $config['connection'], 'event' => 'postGenerateSchema'));

        $container->setAlias('bernard.dbal_connection', 'doctrine.dbal.' . $config['connection'] . '_connection');
    }

    protected function registerIronMQConfiguration($config, $container)
    {
        $container->getDefinition('bernard.driver.ironmq')->replaceArgument(0, new Reference($config['ironmq_service']));
    }

    protected function registerJmsConfiguration($container)
    {
        $definition = new Definition('Bernard\JMSSerializer\EnvelopeHandler');
        $definition->addTag('jms_serializer.subscribing_handler');
        $container->setDefinition('bernard.jms_serializer.envelope_handler', $definition);
    }

    protected function registerMiddlewaresConfiguration($config, $container)
    {
        if ($config['failures']) {
            $container->getDefinition('bernard.middleware.failures')
                ->addTag('bernard.middleware', array('type' => 'consumer'));
        }

        if ($config['error_log']) {
            $container->getDefinition('bernard.middleware.error_log')
                ->addTag('bernard.middleware', array('type' => 'consumer'));
        }

        if ($config['logger']) {
            $container->getDefinition('bernard.middleware.logger')
                ->addTag('bernard.middleware', array('type' => 'consumer'));
        }
    }

    protected function registerPhpRedisConfiguration($config, $container)
    {
        $container->getDefinition('bernard.driver.phpredis')->replaceArgument(0, new Reference($config['phpredis_service']));
    }
}
