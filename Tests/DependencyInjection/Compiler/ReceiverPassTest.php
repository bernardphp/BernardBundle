<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register('bernard.router', 'Bernard\Symfony\ContainerAwareRouter');
    }

    public function testRegisterMultipleTags()
    {
        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array('message' => 'ImportUsers'))
            ->addTag('bernard.receiver', array('message' => 'SendNewsletter'))
            ->addTag('bernard.receiver', array('message' => 'DeleteWorld'));

        $pass = new ReceiverPass();
        $pass->process($this->container);

        $arguments = $this->container->getDefinition('bernard.router')
            ->getArguments();

        $expected = array(
            'ImportUsers' => 'test_receiver',
            'SendNewsletter' => 'test_receiver',
            'DeleteWorld' => 'test_receiver',
        );

        $this->assertCount(2, $arguments);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0]);
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals($expected, $arguments[1]);
    }

    public function testExceptionWhenNameAttributeIsMissing()
    {
        $this->setExpectedException('RuntimeException');

        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array());

        $pass = new ReceiverPass();
        $pass->process($this->container);
    }
}
