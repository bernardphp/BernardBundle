<?php

namespace Bernard\BernardBundle\Tests\DependencyInjection\Compiler;

use Bernard\BernardBundle\DependencyInjection\Compiler\NormalizerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NormalizerPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container
            ->register('bernard.normalizer', 'Normalt\Normalizer\AggregateNormalizer')
            ->addArgument([])
        ;
    }

    public function testRegister()
    {
        $this->container->register('normalizer_one', 'stdClass')->addTag('bernard.normalizer');
        $this->container->register('normalizer_two', 'stdClass')->addTag('bernard.normalizer');

        $pass = new NormalizerPass();
        $pass->process($this->container);

        $refs = $this->container->getDefinition('bernard.normalizer')->getArgument(0);

        $this->assertCount(2, $refs);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $refs[0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $refs[1]);
        $this->assertEquals('normalizer_one', (string) $refs[0]);
        $this->assertEquals('normalizer_two', (string) $refs[1]);
    }
}
