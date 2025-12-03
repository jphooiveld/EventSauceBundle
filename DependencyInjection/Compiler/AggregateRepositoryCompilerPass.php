<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Compiler;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\String\ByteString;

final class AggregateRepositoryCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $snapshotEnabled = $container->getParameter('jphooiveld_eventsauce.snapshot_repository.enabled');

        /** @var class-string[] $classNames */
        $classNames = $container->getParameter('jphooiveld_eventsauce.message_repository.aggregates');

        foreach ($classNames as $className) {
            if (is_a($className, AggregateRoot::class, true)) {
                $reflectionClass    = new ReflectionClass($className);
                $shortClassName     = $reflectionClass->getShortName();
                $servicePostFix     = new ByteString($shortClassName)->snake();
                $repositoryArgument = new ByteString($shortClassName)->camel() . 'Repository';
                $serviceId          = sprintf('jphooiveld_eventsauce.aggregate_repository.%s', $servicePostFix);

                if ($container->hasDefinition($serviceId)) {
                    continue;
                }

                if (is_a($className, AggregateRootWithSnapshotting::class, true)) {
                    if (!$snapshotEnabled) {
                        throw new LogicException('Snapshotting is not enabled.');
                    }

                    $this->createAggregateRootWithSnapshottingService($container, $serviceId, $className, $repositoryArgument);
                    continue;
                }

                $this->createAggregateRootService($container, $serviceId, $className, $repositoryArgument);
                continue;
            }

            throw new LogicException(sprintf('Provided class must implement %s', AggregateRoot::class));
        }
    }

    private function createAggregateRootWithSnapshottingService(ContainerBuilder $container, string $serviceId, string $className, string $repositoryArgument): void
    {
        $innerServiceId = sprintf('%s.inner', $serviceId);

        $this->createAggregateRootService($container, $innerServiceId, $className, $repositoryArgument);

        $arguments = [
            $className,
            new Reference('jphooiveld_eventsauce.message_repository'),
            new Reference('jphooiveld_eventsauce.snapshot_repository'),
            new Reference($innerServiceId),
        ];

        $definition = new Definition(ConstructingAggregateRootRepositoryWithSnapshotting::class, $arguments);
        $container->setDefinition($serviceId, $definition);
        $container->registerAliasForArgument($serviceId, AggregateRootRepositoryWithSnapshotting::class, $repositoryArgument);
    }

    private function createAggregateRootService(ContainerBuilder $container, string $serviceId, string $className, string $repositoryArgument): void
    {
        $arguments = [
            $className,
            new Reference('jphooiveld_eventsauce.message_repository'),
            new Reference('jphooiveld_eventsauce.message_dispatcher'),
            new Reference('jphooiveld_eventsauce.message_decorator'),
        ];

        $definition = new Definition(EventSourcedAggregateRootRepository::class, $arguments);
        $container->setDefinition($serviceId, $definition);
        $container->registerAliasForArgument($serviceId, AggregateRootRepository::class, $repositoryArgument);
    }
}
