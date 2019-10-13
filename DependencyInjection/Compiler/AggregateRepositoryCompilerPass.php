<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class AggregateRepositoryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getParameter('jphooiveld_eventsauce.message_repository.aggregates') as $className) {
            $reflectionClass = new ReflectionClass($className);
            $shortClassName  = $reflectionClass->getShortName();
            $servicePostFix  = preg_replace('~(?<=\\w)([A-Z])~', '_$1', $shortClassName);

            if ($servicePostFix === null) {
                throw new LogicException('Service post fix must not be null');
            }

            $definitionName = sprintf('jphooiveld_eventsauce.aggregate_repository.%s', strtolower($servicePostFix));

            if ($container->hasDefinition($definitionName)) {
                continue;
            }

            $arguments = [
                $className,
                new Reference('jphooiveld_eventsauce.message_repository'),
                new Reference('jphooiveld_eventsauce.message_dispatcher'),
                new Reference('jphooiveld_eventsauce.message_decorator'),
            ];

            $definition = new Definition(ConstructingAggregateRootRepository::class, $arguments);
            $container->setDefinition($definitionName, $definition);
        }
    }
}