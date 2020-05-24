<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection\Compiler;

use Exception;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Aggregate\Order;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AggregateRepositoryCompilerPassTest
 * @package Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection\Compiler
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Configuration
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension
 */
final class AggregateRepositoryCompilerPassTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testAutoConfiguredAggregatedRoot(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $loader->load([[]], $configuration);

        $configuration->setParameter('jphooiveld_eventsauce.message_repository.aggregates', [Order::class]);

        $compiler = new AggregateRepositoryCompilerPass();
        $compiler->process($configuration);

        self::assertTrue($configuration->hasDefinition('jphooiveld_eventsauce.aggregate_repository.order'));
    }
}
