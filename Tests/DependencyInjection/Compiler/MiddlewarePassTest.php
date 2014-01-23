<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\MiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MiddlewarePassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder;
        $this->container->register('bernard.middleware_consumer', 'Bernard\Middleware\MiddlewareBuilder');
        $this->container->register('bernard.middleware_producer', 'Bernard\Middleware\MiddlewareBuilder');
    }

    public function testFactoriesAreAddedToMiddlewareBuilder()
    {
        $this->container->register('my_factory', 'stdClass')
            ->addTag('bernard.middleware', array('type' => 'consumer'))
            ->addTag('bernard.middleware', array('type' => 'producer'));

        $pass = new MiddlewarePass;
        $pass->process($this->container);

        $arguments = $this->container->getDefinition('bernard.middleware_consumer')->getArguments();

        $this->assertCount(1, $arguments);
        $this->assertEquals('my_factory', (string) $arguments[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0][0]);

        $arguments = $this->container->getDefinition('bernard.middleware_producer')->getArguments();

        $this->assertCount(1, $arguments);
        $this->assertEquals('my_factory', (string) $arguments[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0][0]);
    }

    public function testTypeMustBePresentInTag()
    {
        $this->setExpectedException('RuntimeException');

        $this->container->register('my_factory', 'stdClass')
            ->addTag('bernard.middleware', array());

        $pass = new MiddlewarePass;
        $pass->process($this->container);
    }

    public function testTypeMustBeConsumerOrProducer()
    {
        $this->setExpectedException('RuntimeException');

        $this->container->register('my_factory', 'stdClass')
            ->addTag('bernard.middleware', array('type' => 'somethingwrong'));

        $pass = new MiddlewarePass;
        $pass->process($this->container);
    }
}
