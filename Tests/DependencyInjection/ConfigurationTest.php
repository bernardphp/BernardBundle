<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $config = $this->processConfig(array(
            'driver' => 'doctrine',
        ));

        $this->assertEquals('simple', $config['serializer']);

        $this->assertEquals(array('error_log' => false, 'logger' => false, 'failures' => false), $config['middlewares']);
        $this->assertEquals(array('prefetch' => null, 'directory' => '', 'connection' => 'default', 'phpredis_service' => 'snc_redis.bernard', 'ironmq_service' => null, 'queue_map' => array()), $config['options']);
    }

    public function testDriverIsRequired()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->processConfig(array());
    }

    public function testInvalidDriver()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->processConfig(array('driver' => 'non_existent'));
    }

    public function testInvalidSerializer()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->processConfig(array('driver' => 'non_existent'));
    }

    public function testFileDriverRequiresDirectoryOptionToBeSet()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->processConfig(array('driver' => 'file'));
    }

    protected function processConfig($config)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), array($config));
    }
}
