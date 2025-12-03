<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.payload_serializer.constructing', \EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer::class);

    $services->alias('jphooiveld_eventsauce.payload_serializer', 'jphooiveld_eventsauce.payload_serializer.constructing');

    $services->set('jphooiveld_eventsauce.message_serializer.constructing', \EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer::class)
        ->args([
            service('jphooiveld_eventsauce.inflector'),
            service('jphooiveld_eventsauce.payload_serializer'),
        ]);

    $services->alias('jphooiveld_eventsauce.message_serializer', 'jphooiveld_eventsauce.message_serializer.constructing');
};
