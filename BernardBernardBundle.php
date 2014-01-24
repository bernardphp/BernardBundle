<?php

namespace Bernard\BernardBundle;

use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Bernard\BernardBundle\DependencyInjection\Compiler\MiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

class BernardBernardBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new ReceiverPass);
        $builder->addCompilerPass(new MiddlewarePass);
    }

    public function registerCommands(Application $application)
    {
        $application->add($this->container->get('bernard.consume_command'));
        $application->add($this->container->get('bernard.produce_command'));
    }
}
