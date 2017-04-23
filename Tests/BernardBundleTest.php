<?php

namespace Bernard\BernardBundle\Tests;

use Bernard\BernardBundle\BernardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testExtension()
    {
        $bundle = new BernardBundle();

        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\BernardExtension', $bundle->getContainerExtension());
    }

    public function testCompilerPassIsRegistered()
    {
        $bundle = new BernardBundle();
        $bundle->build($this->container);

        $passes = $this->container->getCompilerPassConfig()->getBeforeOptimizationPasses();

        $this->assertCount(2, $passes);
        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass', $passes[0]);
        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\Compiler\NormalizerPass', $passes[1]);
    }
}
