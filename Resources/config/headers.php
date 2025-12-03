<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.date_time_zone', \DateTimeZone::class)
        ->args(['%jphooiveld_eventsauce.time_of_recording.timezone%']);

    $services->set('jphooiveld_eventsauce.clock.system', \EventSauce\Clock\SystemClock::class)
        ->args([service('jphooiveld_eventsauce.date_time_zone')]);

    $services->alias('jphooiveld_eventsauce.clock', 'jphooiveld_eventsauce.clock.system');

    $services->set('jphooiveld_eventsauce.header.default', \EventSauce\EventSourcing\DefaultHeadersDecorator::class)
        ->private()
        ->args([
            service('jphooiveld_eventsauce.inflector'),
            service('jphooiveld_eventsauce.clock'),
        ])
        ->tag('eventsauce.message_decorator');

    $services->set('jphooiveld_eventsauce.message_decorator.chain', \EventSauce\EventSourcing\MessageDecoratorChain::class)
        ->private()
        ->args(['']);

    $services->alias('jphooiveld_eventsauce.message_decorator', 'jphooiveld_eventsauce.message_decorator.chain');
};
