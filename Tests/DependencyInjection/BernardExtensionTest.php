<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\BernardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->extension = new BernardExtension;
        $this->container = new ContainerBuilder;
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
