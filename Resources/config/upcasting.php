<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.upcaster.chain', \EventSauce\EventSourcing\Upcasting\UpcasterChain::class)
        ->private()
        ->args(['']);

    $services->set('jphooiveld_eventsauce.upcaster.message_serializer', \EventSauce\EventSourcing\Upcasting\UpcastingMessageSerializer::class)
        ->private()
        ->decorate('jphooiveld_eventsauce.message_serializer')
        ->args([
            service('.inner'),
            service('jphooiveld_eventsauce.upcaster.chain'),
        ]);

    $services->alias('jphooiveld_eventsauce.upcaster', 'jphooiveld_eventsauce.upcaster.message_serializer');
};
