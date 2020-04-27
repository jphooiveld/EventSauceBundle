<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MessageDecoratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('jphooiveld_eventsauce.message_decorator.chain')) {
            return;
        }

        $definition = $container->getDefinition('jphooiveld_eventsauce.message_decorator.chain');
        $arguments  = [];

        foreach ($container->findTaggedServiceIds('eventsauce.message_decorator') as $id => $tags) {
            $arguments[] = new Reference($id);
        }

        $definition->setArguments($arguments);
    }
}