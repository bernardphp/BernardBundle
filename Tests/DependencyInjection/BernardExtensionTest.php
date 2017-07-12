<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var BernardExtension */
    private $extension;

    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->extension = new BernardExtension();
        $this->container = new ContainerBuilder();

        $this->container->setParameter('kernel.debug', true);
    }

    public function testServicesExists()
    {
        $config = ['driver' => 'doctrine'];
        $this->extension->load([$config], $this->container);

        // Make sure we don't have a dependencies on a real driver.
        $this->container->set('bernard.driver', $this->createMock('Bernard\Driver'));
        $this->container->set('event_dispatcher', $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'));

        // Real services.
        $this->assertInstanceOf('Bernard\Producer', $this->container->get('bernard.producer'));
        $this->assertInstanceOf('Bernard\Consumer', $this->container->get('bernard.consumer'));
        $this->assertInstanceOf('Bernard\Command\ConsumeCommand', $this->container->get('bernard.command.consume'));
        $this->assertInstanceOf('Bernard\Command\ProduceCommand', $this->container->get('bernard.command.produce'));
    }

    public function testListenersAreNotRegisteredByDefault()
    {
        $config = ['driver' => 'doctrine'];
        $this->extension->load([$config], $this->container);

        foreach (['error_log', 'logger', 'failure'] as $listener) {
            $this->assertFalse($this->container->hasDefinition('bernard.listener.'.$listener));
        }
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

    public function testSqsDriverCanBeBuildFromConfiguration()
    {
        $configuredClientService = 'sqs-client-service';
        $configuredQueueMap = ['name1' => 'url1', 'name2' => 'url2'];
        $configuredPrefetchValue = 1;

        $config = [
            'driver' => 'sqs',
            'options' => [
                'sqs_service' => $configuredClientService,
                'sqs_queue_map' => $configuredQueueMap,
                'prefetch' => $configuredPrefetchValue,
            ],
        ];

        $this->extension->load([$config], $this->container);
        $driverDefinition = $this->container->getDefinition('bernard.driver.sqs');

        $this->assertCount(3, $driverDefinition->getArguments());
        $this->assertEquals($configuredClientService, $driverDefinition->getArgument(0));
        $this->assertEquals($configuredQueueMap, $driverDefinition->getArgument(1));
        $this->assertEquals($configuredPrefetchValue, $driverDefinition->getArgument(2));
    }
}
