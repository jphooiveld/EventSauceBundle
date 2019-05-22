<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection;

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
                ->arrayNode('message_dispatcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')
                            ->info('The service to use when dispatching events / messages. If messenger is enabled this will be ignored.')
                            ->defaultValue('jphooiveld_eventsauce.message_dispatcher.synchronous')
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
                    ->end()
                ->end()
                ->arrayNode('message_repository')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('autoconfigure_aggregates')
                            ->info('Autoconfigure aggregate roots to use the default repository implemtations as created by the bundle. Turn this off if you want to create your own implementation.')
                            ->defaultValue(true)
                        ->end()
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
                    ->end()
                ->end()
            ->end()
        ;
        //@formatter:on

        return $treeBuilder;
    }
}