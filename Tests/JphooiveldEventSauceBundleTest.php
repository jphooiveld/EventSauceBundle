<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\ConsumerCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\MessageDecoratorCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\UpcasterCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\ConsumerCompilerPass
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\MessageDecoratorCompilerPass
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\UpcasterCompilerPass
 */
final class JphooiveldEventSauceBundleTest extends TestCase
{
    public function test_compiler_passes(): void
    {
        $container = new ContainerBuilder();
        $bundle    = new JphooiveldEventSauceBundle();
        $bundle->build($container);
        $passes = $container->getCompiler()->getPassConfig()->getPasses();

        $this->assertContainsCompilerPass($passes, ConsumerCompilerPass::class);
        $this->assertContainsCompilerPass($passes, UpcasterCompilerPass::class);
        $this->assertContainsCompilerPass($passes, MessageDecoratorCompilerPass::class);
        $this->assertContainsCompilerPass($passes, AggregateRepositoryCompilerPass::class);
    }

    /**
     * @param CompilerPassInterface[] $passes
     */
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
