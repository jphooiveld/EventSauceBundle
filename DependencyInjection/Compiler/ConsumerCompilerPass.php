<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ConsumerCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('jphooiveld_eventsauce.message_dispatcher.synchronous')) {
            return;
        }

        $definition = $container->getDefinition('jphooiveld_eventsauce.message_dispatcher.synchronous');
        $arguments  = [];

        foreach ($container->findTaggedServiceIds('eventsauce.consumer') as $id => $tags) {
            $arguments[] = new Reference($id);
        }

        $definition->setArguments($arguments);
    }
}