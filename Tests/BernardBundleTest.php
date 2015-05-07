<?php

namespace Bernard\BernardBundle\Tests;

use Bernard\BernardBundle\BernardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

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

    public function testCommandsAreRegistered()
    {
        $this->container->set('bernard.command.consume', new Command('bernard:consume'));
        $this->container->set('bernard.command.produce', new Command('bernard:produce'));

        $application = new Application();

        $bundle = new BernardBundle();
        $bundle->setContainer($this->container);
        $bundle->registerCommands($application);

        $this->assertTrue($application->has('bernard:consume'));
        $this->assertTrue($application->has('bernard:produce'));

        $this->assertTrue($application->has('bernard:debug'));
        $this->assertInstanceOf('Bernard\BernardBundle\Command\DebugCommand', $application->get('bernard:debug'));
    }
}
