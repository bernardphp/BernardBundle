<?php

namespace Bernard\BernardBundle\Tests;

use Bernard\BernardBundle\BernardBernardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class BernardBernardBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPassIsRegistered()
    {
        $container = new ContainerBuilder();

        $bundle = new BernardBernardBundle();
        $bundle->build($container);

        $passes = $container->getCompilerPassConfig()->getBeforeOptimizationPasses();

        $this->assertCount(1, $passes);
        $this->assertInstanceOf('Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass', $passes[0]);
    }

    public function testCommandsAreRegistered()
    {
        $command = $this->getMockBuilder('Symfony\Component\Console\Command\Command')
            ->disableOriginalConstructor()->getMock();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->at(0))->method('get')->with('bernard.consume_command')->will($this->returnValue($command));
        $container->expects($this->at(1))->method('get')->with('bernard.produce_command')->will($this->returnValue($command));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects($this->any())->method('getContainer')->will($this->returnValue($container));

        $application = new Application($kernel);

        $bundle = new BernardBernardBundle;
        $bundle->registerCommands($application);
    }
}
