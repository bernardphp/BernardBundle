<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReceiverPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container
            ->register('bernard.router', 'Bernard\Router\ContainerAwareRouter')
            ->setArguments([new Reference('service_container'), []])
        ;
    }

    public function testRegisterMultipleTags()
    {
        $this->container->register('test_receiver', 'stdClass')
            ->addTag('bernard.receiver', ['message' => 'ImportUsers'])
            ->addTag('bernard.receiver', ['message' => 'SendNewsletter'])
            ->addTag('bernard.receiver', ['message' => 'DeleteWorld'])
        ;

        $pass = new ReceiverPass();
        $pass->process($this->container);

        $arguments = $this->container->getDefinition('bernard.router')->getArguments();

        $expected = [
            'ImportUsers' => 'test_receiver',
            'SendNewsletter' => 'test_receiver',
            'DeleteWorld' => 'test_receiver',
        ];

        $this->assertCount(2, $arguments);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0]);
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals($expected, $arguments[1]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionWhenNameAttributeIsMissing()
    {
        $this->container->register('test_receiver', 'stdClass')->addTag('bernard.receiver', []);

        $pass = new ReceiverPass();
        $pass->process($this->container);
    }
}
