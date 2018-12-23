<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection;

use Bernard\BernardBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
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
            'prefetch' => null,
            'connection' => 'default',
            'directory' => '%kernel.cache_dir%/bernard',
            'phpamqp_service' => 'old_sound_rabbit_mq.connection.default',
            'phpamqp_exchange' => null,
            'phpamqp_default_message_parameters' => [],
            'phpredis_service' => 'snc_redis.bernard',
            'predis_service' => 'snc_redis.bernard',
            'ironmq_service' => null,
            'sqs_service' => null,
            'sqs_queue_map' => [],
            'pheanstalk_service' => null,
            'custom_service' => null,
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
     * @expectedExceptionMessage The "phpamqp_exchange" option must be defined when using the "phpamqp" driver.
     */
    public function testPhpAmqpDriverRequiresExchangeOptionToBeSet()
    {
        $this->processConfig(['driver' => 'phpamqp']);
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
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "sqs_service" option must be defined when using the "sqs" driver.
     */
    public function testSqsDriverRequiresServiceOptionToBeSet()
    {
        $this->processConfig(['driver' => 'sqs']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "pheanstalk_service" option must be defined when using the "pheanstalk" driver.
     */
    public function testPheanstalkDriverRequiresServiceOptionToBeSet()
    {
        $this->processConfig(['driver' => 'pheanstalk']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "custom_service" option must be defined when using the "custom" driver.
     */
    public function testCustomDriverRequiresServiceOptionToBeSet()
    {
        $this->processConfig(['driver' => 'custom']);
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
