<?php

namespace Bernard\BernardBundle\Middleware;

use Bernard\BernardBundle\DataCollector\ProducerDataCollector;
use Bernard\Envelope;
use Bernard\Middleware;
use Bernard\Queue;

class DataCollectorMiddleware implements Middleware
{
    protected $next;
    protected $collector;

    public function __construct(Middleware $next, $collector)
    {
        $this->next = $next;
        $this->collector = $collector;
    }

    public function call(Envelope $envelope, Queue $queue)
    {
        $this->collector->add($envelope, $queue);

        $this->next->call($envelope, $queue);
    }
}
