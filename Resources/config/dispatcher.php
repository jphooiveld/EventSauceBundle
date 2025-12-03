<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.message_dispatcher.synchronous', \EventSauce\EventSourcing\SynchronousMessageDispatcher::class)
        ->args(['']);

    $services->alias('jphooiveld_eventsauce.message_dispatcher', 'jphooiveld_eventsauce.message_dispatcher.synchronous');
};
