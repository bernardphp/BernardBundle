<?php

namespace Bernard\BernardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
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

        $this->registerMiddlewaresConfiguration($config['middlewares'], $container);
    }

    protected function registerFlatFileConfiguration($config, $container)
    {
        $container->getDefinition('bernard.driver.file')->replaceArgument(0, $config['directory']);
    }

    protected function registerDoctrineConfiguration($config, $container)
    {
        $container->getDefinition('bernard.schema_listener')
            ->addTag('doctrine.event_listener', array('lazy' => true, 'connection' => $config['connection'], 'event' => 'postGenerateSchema'));

        $container->setAlias('bernard.dbal_connection', 'doctrine.dbal.' . $config['connection'] . '_connection');
    }

    protected function registerMiddlewaresConfiguration($config, $contiainer)
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
}
