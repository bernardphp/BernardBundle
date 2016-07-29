<?php

namespace Bernard\BernardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('bernard:debug')
            ->setDescription('Displays a table of receivers that are registered with "bernard.receiver" tag.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $router = $this->getContainer()->get('bernard.router');

        $r = new \ReflectionProperty($router, 'receivers');
        $r->setAccessible(true);

        $rows = [];
        foreach ($r->getValue($router) as $key => $val) {
            $rows[] = [$key, $val];
        }

        $headers = ['Message', 'Service'];

        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            $table = new Table($output);
            $table
                ->setHeaders($headers)
                ->addRows($rows)
                ->render()
            ;
        } else {
            /** @var \Symfony\Component\Console\Helper\TableHelper $helper */
            $helper = $this->getHelper('table');
            $helper
                ->setHeaders($headers)
                ->addRows($rows)
                ->render($output)
            ;
        }
    }
}
