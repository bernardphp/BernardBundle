<?php

namespace Bernard\BernardBundle\Tests;

use Bernard\BernardBundle\BernardBernardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class BernardBernardBundleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder;
    }

    public function testCompilerPassIsRegistered()
    {
        $bundle = new BernardBernardBundle();
        $bundle->build($this->container);

        $passes = $this->container->getCompilerPassConfig()->getBeforeOptimizationPasses();

        $this->assertCount(2, $passes);
        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass', $passes[0]);
        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\Compiler\MiddlewarePass', $passes[1]);
    }

    public function testCommandsAreRegistered()
    {
        $this->container->set('bernard.consume_command', new Command('bernard:consume'));
        $this->container->set('bernard.produce_command', new Command('bernard:produce'));

        $application = new Application();

        $bundle = new BernardBernardBundle;
        $bundle->setContainer($this->container);
        $bundle->registerCommands($application);

        $this->assertTrue($application->has('bernard:consume'));
        $this->assertTrue($application->has('bernard:produce'));
    }
}
