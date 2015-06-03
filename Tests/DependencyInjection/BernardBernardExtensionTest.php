<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardBernardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

class BernardBernardExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BernardBernardExtension
     */
    protected $extension;

    /**
     * @var ContainerBuilder
     */
    protected $container;

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

    public function testDoctrineEventListenerIsAdded()
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

    public function testSqsDriverCanBeBuildFromConfiguration()
    {
        $configuredQueueMap = array('name1' => 'url1', 'name2' => 'url2');
        $configuredPrefetch = 5;
        $configuredRegion = 'test-region';
        $configuredKey = 'test-key';
        $configuredSecret = 'test-secret';

        $config = array(
            'driver' => 'sqs',
            'options' => array(
                'queue_map' => $configuredQueueMap,
                'prefetch' => $configuredPrefetch,
            ),
            'sqs' => array(
                'region' => $configuredRegion,
                'key' => $configuredKey,
                'secret' => $configuredSecret,
            ),
        );

        $this->extension->load(array($config), $this->container);
        $driverDefinition = $this->container->getDefinition('bernard.driver.sqs');

        /** @var Definition $resultingSqsClientArgument */
        $resultingSqsClientArgument = $driverDefinition->getArgument(0);
        if (Kernel::MAJOR_VERSION == 2 && Kernel::MINOR_VERSION < 6) {
            $this->assertSame('Aws\Sqs\SqsClient', $resultingSqsClientArgument->getFactoryClass());
            $this->assertSame('factory', $resultingSqsClientArgument->getFactoryMethod());
        } else {
            $this->assertSame(array('Aws\Sqs\SqsClient', 'factory'), $resultingSqsClientArgument->getFactory());
        }

        $sqsClientFactoryArguments = $resultingSqsClientArgument->getArguments();
        $sqsClientFactoryConfiguration = $sqsClientFactoryArguments[0];
        $this->assertSame($configuredRegion, $sqsClientFactoryConfiguration['region']);
        $this->assertSame($configuredKey, $sqsClientFactoryConfiguration['key']);
        $this->assertSame($configuredSecret, $sqsClientFactoryConfiguration['secret']);

        $resultingQueueMapArgument = $driverDefinition->getArgument(1);
        $this->assertEquals($configuredQueueMap, $resultingQueueMapArgument);

        $resultingPrefetchArgument = $driverDefinition->getArgument(2);
        $this->assertEquals($configuredPrefetch, $resultingPrefetchArgument);
    }
}
