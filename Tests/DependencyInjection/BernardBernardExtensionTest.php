<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardBernardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardBernardExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->extension = new BernardBernardExtension;
        $this->container = new ContainerBuilder;
    }

    public function testServicesExists()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        // make sure we dont have a dependencies on a real driver.
        $this->container->set('bernard.driver', $this->getMock('Bernard\Driver'));

        // Real services
        $this->assertInstanceOf('Bernard\Producer', $this->container->get('bernard.producer'));
        $this->assertInstanceOf('Bernard\Consumer', $this->container->get('bernard.consumer'));
        $this->assertInstanceOf('Bernard\Command\ConsumeCommand', $this->container->get('bernard.consume_command'));
        $this->assertInstanceOf('Bernard\Command\ProduceCommand', $this->container->get('bernard.produce_command'));
    }

    public function testMiddlewaresHaveMiddlewareTag()
    {
        $config = array(
            'driver' => 'doctrine',
            'middlewares' => array('error_log' => true, 'logger' => true, 'failures' => true),
        );

        $this->extension->load(array($config), $this->container);

        $definition = $this->container->getDefinition('bernard.middleware.failures');
        $this->assertTrue($definition->hasTag('bernard.middleware'));
        $this->assertEquals(array(array('type' => 'consumer')), $definition->getTag('bernard.middleware'));

        $definition = $this->container->getDefinition('bernard.middleware.error_log');
        $this->assertTrue($definition->hasTag('bernard.middleware'));
        $this->assertEquals(array(array('type' => 'consumer')), $definition->getTag('bernard.middleware'));

        $definition = $this->container->getDefinition('bernard.middleware.logger');
        $this->assertTrue($definition->hasTag('bernard.middleware'));
        $this->assertEquals(array(array('type' => 'consumer')), $definition->getTag('bernard.middleware'));
    }

    public function testDoctrinEventListenerIsAdded()
    {
        $config = array_filter(array('driver' => 'doctrine', 'options' => array('connection' => 'bernard')));

        $this->extension->load(array($config), $this->container);

        $definition = $this->container->getDefinition('bernard.schema_listener');

        $expected = array(
            'event' => 'postGenerateSchema',
            'connection' => 'bernard',
            'lazy' => true,
        );

        $this->assertTrue($definition->hasTag('doctrine.event_listener'));
        $this->assertEquals(array($expected), $definition->getTag('doctrine.event_listener'));
        $this->assertEquals('doctrine.dbal.bernard_connection', $this->container->getAlias('bernard.dbal_connection'));
    }

    public function testDirectoryIsAddedToFileDriver()
    {
        $this->extension->load(array(array('driver' => 'file', 'options' => array('directory' => __DIR__))), $this->container);

        $definition = $this->container->getDefinition('bernard.driver.file');

        $this->assertCount(1, $definition->getArguments());
        $this->assertEquals(__DIR__, $definition->getArgument(0));
    }

    public function testSerializerAliases()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        $alias = $this->container->getAlias('bernard.serializer');

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $alias);
        $this->assertEquals('bernard.serializer.simple', (string) $alias);
    }

    public function testDriverIsAliased()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        $alias = $this->container->getAlias('bernard.driver');

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $alias);
        $this->assertEquals('bernard.driver.doctrine', (string) $alias);
    }
}
