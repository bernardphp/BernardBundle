<?php

namespace Bernard\BernardBundle\Middleware;

use Bernard\Middleware;
use Bernard\BernardBundle\DataCollector\ProducerDataCollector;

class DataCollectorFactory
{
    protected $collector;

    public function __construct(ProducerDataCollector $collector)
    {
        $this->collector = $collector;
    }

    public function __invoke(Middleware $next)
    {
        return new DataCollectorMiddleware($next, $this->collector);
    }
}
