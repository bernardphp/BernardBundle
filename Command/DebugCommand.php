<?php

namespace Bernard\BernardBundle\Command;

use Bernard\Router;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final since 2.1
 */
class DebugCommand extends ContainerAwareCommand
{
    /**
     * @var Router
     */
    private $router;

    public function __construct($router = null)
    {
        parent::__construct();

        if (!$router instanceof Router) {
            @trigger_error(sprintf('Passing a command name as the first argument of "%s" is deprecated since version symfony 3.4 and will be removed in symfony 4.0. If the command was registered by convention, make it a service instead as this will be the only supported way in bernard bundle 3.0. ', __METHOD__), E_USER_DEPRECATED);

            $this->setName(null === $router ? 'bernard:debug' : $router);

            return;
        }

        $this->router = $router;
    }

    public function configure()
    {
        $this
            ->setName('bernard:debug')
            ->setDescription('Displays a table of receivers that are registered with "bernard.receiver" tag.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->router) {
            $this->router = $this->getContainer()->get('bernard.router');
        }

        $r = new \ReflectionProperty($this->router, 'receivers');
        $r->setAccessible(true);

        $rows = [];
        foreach ($r->getValue($this->router) as $key => $val) {
            $rows[] = [$key, $val];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Message', 'Service'])
            ->addRows($rows)
            ->render()
        ;
    }
}
