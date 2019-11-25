<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
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
        $snapshotEnabled = $container->getParameter('jphooiveld_eventsauce.snapshot_repository.enabled');

        foreach ($container->getParameter('jphooiveld_eventsauce.message_repository.aggregates') as $className) {
            if (is_a($className, AggregateRoot::class, true)) {
                $reflectionClass = new ReflectionClass($className);
                $shortClassName  = $reflectionClass->getShortName();
                $servicePostFix  = preg_replace('~(?<=\\w)([A-Z])~', '_$1', $shortClassName);

                if ($servicePostFix === null) {
                    throw new LogicException('Service post fix must not be null');
                }

                $serviceId = sprintf('jphooiveld_eventsauce.aggregate_repository.%s', strtolower($servicePostFix));

                if ($container->hasDefinition($serviceId)) {
                    continue;
                }

                if (is_a($className, AggregateRootWithSnapshotting::class, true)) {
                    if (!$snapshotEnabled) {
                        throw new LogicException('Snapshotting is not enabled.');
                    }

                    $this->createAggregateRootWithSnapshottingService($container, $serviceId, $className);
                    continue;
                }

                $this->createAggregateRootService($container, $serviceId, $className);
                continue;
            }

            throw new LogicException(sprintf('Provided class must implement %s', AggregateRoot::class));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param AggregateRootWithSnapshotting|string $className
     */
    private function createAggregateRootWithSnapshottingService(ContainerBuilder $container, string $serviceId, $className): void
    {
        $innerServiceId = sprintf('%s.inner', $serviceId);

        $this->createAggregateRootService($container, $innerServiceId, $className);

        $arguments = [
            $className,
            new Reference('jphooiveld_eventsauce.message_repository'),
            new Reference('jphooiveld_eventsauce.snapshot_repository'),
            new Reference($innerServiceId),
        ];

        $definition = new Definition(ConstructingAggregateRootRepositoryWithSnapshotting::class, $arguments);
        $container->setDefinition($serviceId, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param AggregateRoot|string $className
     */
    private function createAggregateRootService(ContainerBuilder $container, string $serviceId, $className): void
    {
        $arguments = [
            $className,
            new Reference('jphooiveld_eventsauce.message_repository'),
            new Reference('jphooiveld_eventsauce.message_dispatcher'),
            new Reference('jphooiveld_eventsauce.message_decorator'),
        ];

        $definition = new Definition(ConstructingAggregateRootRepository::class, $arguments);
        $container->setDefinition($serviceId, $definition);
    }
}
