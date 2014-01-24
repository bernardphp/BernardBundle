<?php

namespace Bernard\BernardBundle;

use Bernard\BernardBundle\Command\DebugCommand;
use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Bernard\BernardBundle\DependencyInjection\Compiler\MiddlewarePass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BernardBernardBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new ReceiverPass);
        $builder->addCompilerPass(new MiddlewarePass);
    }

    public function registerCommands(Application $application)
    {
        parent::registerCommands($application);

        $application->add($this->container->get('bernard.consume_command'));
        $application->add($this->container->get('bernard.produce_command'));
    }
}
