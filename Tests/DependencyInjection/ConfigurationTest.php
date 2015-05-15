<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $config = $this->processConfig(['driver' => 'doctrine']);

        $this->assertEquals([
            'error_log' => false,
            'logger' => [
                'enabled' => false,
                'service' => 'logger',
            ],
            'failure' => [
                'enabled' => false,
                'queue_name' => 'failed',
            ],
        ], $config['listeners']);

        $this->assertEquals([
            'connection'       => 'default',
            'directory'        => null,
            'phpredis_service' => 'snc_redis.bernard',
            'predis_service'   => 'snc_redis.bernard',
            'ironmq_service'   => null,
        ], $config['options']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "driver" at path "bernard" must be configured.
     */
    public function testEmpty()
    {
        $this->processConfig([]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidDriver()
    {
        $this->processConfig(['driver' => 'non_existent']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "directory" option must be defined when using the "file" driver.
     */
    public function testFileDriverRequiresDirectoryOptionToBeSet()
    {
        $this->processConfig(['driver' => 'file']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "ironmq_service" option must be defined when using the "ironmq" driver.
     */
    public function testIronMqDriverRequiresServiceOptionToBeSet()
    {
        $this->processConfig(['driver' => 'ironmq']);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function processConfig(array $config)
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
