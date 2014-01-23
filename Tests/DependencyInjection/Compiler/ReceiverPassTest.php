<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder;
        $this->container->register('bernard.router', 'Bernard\Symfony\ContainerAwareRouter');
    }

    public function testRegisterMultipleTags()
    {
        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array('name' => 'ImportUsers'))
            ->addTag('bernard.receiver', array('name' => 'SendNewsletter'))
            ->addTag('bernard.receiver', array('name' => 'DeleteWorld'));

        $pass = new ReceiverPass;
        $pass->process($this->container);

        $arguments = $this->container->getDefinition('bernard.router')
            ->getArguments();

        $expected = array(
            'ImportUsers' => 'test_receiver',
            'SendNewsletter' => 'test_receiver',
            'DeleteWorld' => 'test_receiver',
        );

        $this->assertCount(1, $arguments);
        $this->assertEquals($expected, $arguments[0]);
    }

    public function testExceptionWhenNameAttributeIsMissing()
    {
        $this->setExpectedException('RuntimeException');

        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array());

        $pass = new ReceiverPass;
        $pass->process($this->container);
    }
}
