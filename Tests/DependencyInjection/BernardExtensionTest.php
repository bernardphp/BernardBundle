<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var BernardExtension */
    private $extension;

    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->extension = new BernardExtension();
        $this->container = new ContainerBuilder();
    }

    public function testServicesExists()
    {
        $config = ['driver' => 'doctrine'];
        $this->extension->load([$config], $this->container);

        // Make sure we don't have a dependencies on a real driver.
        $this->container->set('bernard.driver', $this->getMock('Bernard\Driver'));
        $this->container->set('event_dispatcher', $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'));

        // Real services.
        $this->assertInstanceOf('Bernard\Producer', $this->container->get('bernard.producer'));
        $this->assertInstanceOf('Bernard\Consumer', $this->container->get('bernard.consumer'));
        $this->assertInstanceOf('Bernard\Command\ConsumeCommand', $this->container->get('bernard.command.consume'));
        $this->assertInstanceOf('Bernard\Command\ProduceCommand', $this->container->get('bernard.command.produce'));
    }

    public function testListenersHaveSubscriberTag()
    {
        $config = [
            'driver' => 'doctrine',
            'listeners' => [
                'error_log' => true,
                'logger'    => true,
                'failure'   => true,
            ],
        ];
        $this->extension->load([$config], $this->container);

        foreach (['error_log', 'logger', 'failure'] as $listener) {
            $definition = $this->container->getDefinition('bernard.listener.'.$listener);
            $this->assertTrue($definition->hasTag('kernel.event_subscriber'));
        }
    }

    public function testDoctrineEventListenerIsAdded()
    {
        $config = [
            'driver' => 'doctrine',
            'options' => [
                'connection' => 'bernard',
            ],
        ];
        $this->extension->load([$config], $this->container);

        $definition = $this->container->getDefinition('bernard.listener.doctrine_schema');

        $expected = [
            'event'      => 'postGenerateSchema',
            'connection' => 'bernard',
            'lazy'       => true,
        ];

        $this->assertTrue($definition->hasTag('doctrine.event_listener'));
        $this->assertEquals([$expected], $definition->getTag('doctrine.event_listener'));
        $this->assertEquals('doctrine.dbal.bernard_connection', $this->container->getAlias('bernard.dbal.connection'));
    }

    public function testDirectoryIsAddedToFileDriver()
    {
        $config = [
            'driver' => 'file',
            'options' => [
                'directory' => __DIR__,
            ],
        ];
        $this->extension->load([$config], $this->container);

        $definition = $this->container->getDefinition('bernard.driver.file');

        $this->assertCount(1, $definition->getArguments());
        $this->assertEquals(__DIR__, $definition->getArgument(0));
    }

    public function testDriverIsAliased()
    {
        $config = ['driver' => 'doctrine'];
        $this->extension->load([$config], $this->container);

        $alias = $this->container->getAlias('bernard.driver');

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $alias);
        $this->assertEquals('bernard.driver.doctrine', (string) $alias);
    }
}
