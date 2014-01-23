<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder;
        $this->container->register('bernard.router', 'Bernard\Symfony\ContainerAwareRouter');
    }

    public function testReceiverTagsAreAddedToRouter()
    {
        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array('name' => 'ImportUsers'));

        $pass = new ReceiverPass;
        $pass->process($this->container);

        $calls = $this->container->getDefinition('bernard.router')->getMethodCalls();

        $this->assertEquals('add', $calls[0][0]);
        $this->assertCount(2, $calls[0][1]); // two arguments the MessageName and then the reciever
        $this->assertEquals('ImportUsers', $calls[0][1][0]);
        $this->assertEquals('test_receiver', $calls[0][1][1]);
    }

    public function testRegisterMultipleTags()
    {
        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', array('name' => 'ImportUsers'))
            ->addTag('bernard.receiver', array('name' => 'SendNewsletter'))
            ->addTag('bernard.receiver', array('name' => 'DeleteWorld'));

        $pass = new ReceiverPass;
        $pass->process($this->container);

        $this->assertCount(3, $this->container->getDefinition('bernard.router')->getMethodCalls());
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
