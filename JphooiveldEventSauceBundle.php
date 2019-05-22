<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\ConsumerCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\DelegatableUpcasterCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\MessageDecoratorCompilerPass;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler\AggregateRepositoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class JphooiveldEventSauceBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConsumerCompilerPass());
        $container->addCompilerPass(new DelegatableUpcasterCompilerPass());
        $container->addCompilerPass(new MessageDecoratorCompilerPass());
        $container->addCompilerPass(new AggregateRepositoryCompilerPass());
   }
}