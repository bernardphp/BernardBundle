<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardExtension;
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

        $this->assertTrue($this->container->hasDefinition('bernard.router'));
    }

    public function testInvalidDriver()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->extension->load(array(array('driver' => 'invalid')), $this->container);
    }

    public function testInvalidSerializer()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->extension->load(array(array('driver' => 'doctrine', 'serializer' => 'hopefully not valid')), $this->container);
    }

    public function testFileDriverRequiresDirectoryOptionToBeSet()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->extension->load(array(array('driver' => 'file')), $this->container);
    }

    public function testDoctrinEventListenerIsAdded()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        $definition = $this->container->getDefinition('bernard.schema_listener');

        $this->assertTrue($definition->hasTag('doctrine.event_listener'));
        $this->assertEquals(array(array(
            'event' => 'postGenerateSchema',
            'connection' => 'bernard',
            'lazy' => true,
        )), $definition->getTag('doctrine.event_listener'));
    }

    public function testDirectoryIsAddedToFileDriver()
    {
        $this->extension->load(array(array('driver' => 'file', 'directory' => __DIR__)), $this->container);

        $definition = $this->container->getDefinition('bernard.driver.file');

        $this->assertCount(1, $definition->getArguments());
        $this->assertEquals(__DIR__, $definition->getArgument(0));
    }

    public function testDefaultSerializer()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        $alias = $this->container->getAlias('bernard.serializer');

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $alias);
        $this->assertEquals('bernard.serializer.simple', (string) $alias);
    }

    public function testDriverIsRequired()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->extension->load(array(), $this->container);
    }

    public function testDriverIsAliased()
    {
        $this->extension->load(array(array('driver' => 'doctrine')), $this->container);

        $alias = $this->container->getAlias('bernard.driver');

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Alias', $alias);
        $this->assertEquals('bernard.driver.doctrine', (string) $alias);
    }
}
