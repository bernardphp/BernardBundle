<?php

namespace Bernard\BernardBundle\DataCollector;

use Bernard\Envelope;
use Bernard\Queue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProducerDataCollector extends \Symfony\Component\HttpKernel\DataCollector\DataCollector
{
    public function __construct()
    {
        $this->data['messages'] = array();
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        // part of the interface apparently.
    }

    public function all()
    {
        return $this->data['messages'];
    }

    public function count()
    {
        return array_sum(array_map(function ($messages) { return count($messages); }, $this->data['messages']));
    }

    public function add(Envelope $envelope, Queue $queue)
    {
        $this->data['messages'][(string) $queue][] = $envelope->getMessage();
    }

    public function getName()
    {
        return 'bernard';
    }
}
