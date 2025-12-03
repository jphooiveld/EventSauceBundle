<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.table_schema.default', \EventSauce\MessageRepository\TableSchema\DefaultTableSchema::class)
        ->private();

    $services->set('jphooiveld_eventsauce.table_schema.legacy', \EventSauce\MessageRepository\TableSchema\LegacyTableSchema::class)
        ->private();

    $services->alias('jphooiveld_eventsauce.table_schema', 'jphooiveld_eventsauce.table_schema.default');

    $services->set('jphooiveld_eventsauce.uuid_encoder.string', \EventSauce\UuidEncoding\StringUuidEncoder::class)
        ->private();

    $services->set('jphooiveld_eventsauce.uuid_encoder.binary', \EventSauce\UuidEncoding\BinaryUuidEncoder::class)
        ->private();

    $services->alias('jphooiveld_eventsauce.uuid_encoder', 'jphooiveld_eventsauce.uuid_encoder.string');
};
