<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\ConsumerCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\DelegatableUpcasterCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\MessageDecoratorCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class JphooiveldEventSauceBundleTest
 * @package Jphooiveld\Bundle\EventSauceBundle\Tests
 * @covers \Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle
 */
final class JphooiveldEventSauceBundleTest extends TestCase
{
    public function testCompilerPasses(): void
    {
        $container = new ContainerBuilder();
        $bundle    = new JphooiveldEventSauceBundle();
        $bundle->build($container);
        $passes = $container->getCompiler()->getPassConfig()->getPasses();

        $this->assertContainsCompilerPass($passes, ConsumerCompilerPass::class);
        $this->assertContainsCompilerPass($passes, DelegatableUpcasterCompilerPass::class);
        $this->assertContainsCompilerPass($passes, MessageDecoratorCompilerPass::class);
        $this->assertContainsCompilerPass($passes, AggregateRepositoryCompilerPass::class);
    }

    private function assertContainsCompilerPass(array $passes, string $class): void
    {
        $found = false;

        foreach ($passes as $pass) {
            if (get_class($pass) === $class) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, sprintf('No compiler pass %s found in bundle', $class));
    }
}
