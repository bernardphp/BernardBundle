<?php

namespace Bernard\BernardBundle;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

class BernardBernardBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new ReceiverPass);
    }

    public function registerCommands(Application $application)
    {
        // This is not pretty, but works
        $container = $application->getKernel()->getContainer();

        $application->add($container->get('bernard.consume_command'));
        $application->add($container->get('bernard.produce_command'));
    }
}
