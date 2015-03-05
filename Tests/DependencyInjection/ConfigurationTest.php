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
        $this->assertEquals(array('prefetch' => null, 'directory' => '', 'connection' => 'default', 'queue_map' => array()), $config['options']);
        $this->assertEquals(array('region' => null, 'key' => null, 'secret' => null), $config['sqs']);
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

    public function testSqsDriverWorksWithRequiredOptionsSet()
    {
        $this->setExpectedException(null);

        $this->processConfig($this->createValidSqsConfiguration());
    }

    public function testSqsDriverRequiresRegionOptionToBeSet()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $configurationWithoutRegion = $this->createValidSqsConfiguration();
        $configurationWithoutRegion['options']['sqs']['region'] = null;
        $this->processConfig($configurationWithoutRegion);
    }

    public function testSqsDriverRequiresKeyOptionToBeSet()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $configurationWithoutKey = $this->createValidSqsConfiguration();
        $configurationWithoutKey['options']['sqs']['key'] = null;
        $this->processConfig($configurationWithoutKey);
    }

    public function testSqsDriverRequiresSecretOptionToBeSet()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $configurationWithoutSecret = $this->createValidSqsConfiguration();
        $configurationWithoutSecret['options']['sqs']['secret'] = null;
        $this->processConfig($configurationWithoutSecret);
    }

    protected function processConfig($config)
    {
        $processor = new Processor;

        return $processor->processConfiguration(new Configuration, array($config));
    }

    /**
     * @return array(string => string|array)
     */
    private function createValidSqsConfiguration()
    {
        return array(
            'driver' => 'sqs',
            'sqs' => array(
                'region' => 'test-region',
                'key' => 'test-key',
                'secret' => 'test-secret',
            ),
        );
    }
}
