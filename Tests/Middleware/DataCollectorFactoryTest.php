<?php

namespace Bernard\BernardBundle\Tests\Middleware;

use Bernard\BernardBundle\Middleware\DataCollectorFactory;

class DataCollectorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesMiddleware()
    {
        $collector = $this->getMockBuilder('Bernard\BernardBundle\DataCollector\ProducerDataCollector')
            ->disableOriginalConstructor()->getMock();

        $middleware = $this->getMock('Bernard\Middleware');

        $factory = new DataCollectorFactory($collector);
        $this->assertInstanceOf('Bernard\BernardBundle\Middleware\DataCollectorMiddleware', $factory->__invoke($middleware));
    }
}
