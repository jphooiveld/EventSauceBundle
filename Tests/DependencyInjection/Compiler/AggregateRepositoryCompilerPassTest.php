<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection\Compiler;

use Exception;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Aggregate\Order;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AggregateRepositoryCompilerPassTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testAutoConfigureAggregateRootOn()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $loader->load([[]], $configuration);

        $definition = new Definition(Order::class);
        $definition->addTag('eventsauce.aggregate_repository');

        $configuration->setDefinition(Order::class, $definition);

        $compiler = new AggregateRepositoryCompilerPass();
        $compiler->process($configuration);

        $this->assertTrue($configuration->hasDefinition('jphooiveld_eventsauce.aggregate_repository.order'));
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testAutoConfigureAggregateRootOff()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = [];

        $config['message_repository']['autoconfigure_aggregates'] = false;

        $loader->load([$config], $configuration);

        $definition = new Definition(Order::class);
        $definition->addTag('eventsauce.aggregate_repository');

        $configuration->setDefinition(Order::class, $definition);

        $compiler = new AggregateRepositoryCompilerPass();
        $compiler->process($configuration);

        $this->assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.aggregate_repository.order'));
    }
}