<?php

namespace Bernard\BernardBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
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

        $maxWidths = array(
            'name' => 4,
            'service' => 7,
        );

        $rows = array();

        foreach ($r->getValue($router) as $name => $id) {
            $maxWidths['name'] = max(4, strlen($name));
            $maxWidths['service'] = max(7, strlen($id));

            $rows[] = array($name, $id);
        }

        $header = '| <info>%-' . $maxWidths['name'] . '.' . $maxWidths['name'] . 's</info> | <info>%-' . $maxWidths['service'] . '.' . $maxWidths['service'] . 's</info> |';
        $pattern = '| <comment>%-' . $maxWidths['name'] . '.' . $maxWidths['name'] . 's</comment> | <comment>%-' . $maxWidths['service'] . '.' . $maxWidths['service'] . 's</comment> |';

        $this->writeLineDelimiter($output, $maxWidths);

        $output->writeln(sprintf($header, 'Message', 'Service'));

        $this->writeLineDelimiter($output, $maxWidths);

        foreach ($rows as $row) {
            $output->writeln(sprintf($pattern, $row[0], $row[1]));
            $this->writeLineDelimiter($output, $maxWidths);
        }
    }

    protected function writeLineDelimiter($output, $maxWidths)
    {
        $output->writeln('+' . str_repeat('-', $maxWidths['name'] + 2) . '+' . str_repeat('-', $maxWidths['service'] + 2) . '+');
    }
}
