<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.message_repository.doctrine', \EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineUuidV4MessageRepository::class)
        ->private()
        ->args([
            '',
            '%jphooiveld_eventsauce.repository.doctrine.table%',
            service('jphooiveld_eventsauce.message_serializer'),
            '%jphooiveld_eventsauce.repository.doctrine.json_encode_options%',
            service('jphooiveld_eventsauce.table_schema'),
            service('jphooiveld_eventsauce.uuid_encoder'),
        ]);
};
