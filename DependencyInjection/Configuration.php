<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection;

use EventSauce\EventSourcing\AggregateRoot;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('jphooiveld_eventsauce');

        //@formatter:off
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('time_of_recording')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('timezone')
                            ->info('The timezone to use for recorded messages, defaults to PHP ini setting.')
                            ->defaultValue(ini_get('date.timezone'))
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('messenger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Use symfony messenger instead of EventSauce\'s default dispatcher (needs package symfony/messenger installed).')
                            ->defaultValue(false)
                        ->end()
                        ->scalarNode('service_bus')
                            ->info('The name of the messenger command bus service to use.')
                            ->defaultValue('messenger.bus.events')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('message_repository')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')
                            ->info('Override if you don\'t want to use the default doctrine message repository. If doctrine is enabled this will be ignored.')
                            ->defaultValue('jphooiveld_eventsauce.message_repository.doctrine')
                        ->end()
                        ->arrayNode('doctrine')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Use doctrine as default message repository. If you turn this off you must provide your own service.')
                                    ->defaultValue(true)
                                ->end()
                                ->scalarNode('connection')
                                    ->info('Service that implements a doctrine connection. We assume doctrine bundle default here.')
                                    ->defaultValue('doctrine.dbal.default_connection')
                                ->end()
                                ->scalarNode('table')
                                    ->info('The table name in the database to store the messages.')
                                    ->defaultValue('event')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('aggregates')
                            ->info('Configure repositories for provided aggregate roots.')
                            ->scalarPrototype()
                                ->validate()
                                    ->ifTrue(static function ($value) {
                                        return !is_a($value, AggregateRoot::class, true);
                                    })
                                    ->thenInvalid('Class %s must be valid class and implement interface EventSauce\EventSourcing\AggregateRoot')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                 ->arrayNode('snapshot_repository')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enables snapshotting. Aggregates that implement snapshotting will be configured.')
                            ->defaultValue(false)
                        ->end()
                        ->scalarNode('service')
                            ->info('The service to use for the snapshot repository')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
           ->end()
        ;
        //@formatter:on

        return $treeBuilder;
    }
}