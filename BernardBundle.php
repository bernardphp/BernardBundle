<?php

namespace Bernard\BernardBundle;

use Bernard\BernardBundle\DependencyInjection\Compiler\NormalizerPass;
use Bernard\BernardBundle\DependencyInjection\Compiler\ReceiverPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BernardBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder
            ->addCompilerPass(new ReceiverPass())
            ->addCompilerPass(new NormalizerPass())
        ;
    }

    public function registerCommands(Application $application)
    {
        parent::registerCommands($application);
    }
}
