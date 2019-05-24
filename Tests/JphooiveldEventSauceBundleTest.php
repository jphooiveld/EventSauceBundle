<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\ConsumerCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\DelegatableUpcasterCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\MessageDecoratorCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JphooiveldEventSauceBundleTest extends TestCase
{
    public function testCompilerPasses()
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

    private function assertContainsCompilerPass(array $passes, string $class)
    {
        $found = false;

        foreach ($passes as $pass) {
            if (get_class($pass) === $class) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, sprintf('No compiler pass %s found in bundle', $class));
    }
}