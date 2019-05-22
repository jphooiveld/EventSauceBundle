<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DelegatableUpcasterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('jphooiveld_eventsauce.delegatable_upcaster.delegator')) {
            return;
        }

        $definition = $container->getDefinition('jphooiveld_eventsauce.delegatable_upcaster.delegator');
        $arguments  = [];

        foreach ($container->findTaggedServiceIds('eventsauce.delegatable_upcaster') as $id => $tags) {
            $arguments[] = new Reference($id);
        }

        $definition->setArguments($arguments);
    }
}