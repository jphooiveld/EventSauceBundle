<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.inflector.dot_separated_snake_case', \EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector::class);

    $services->alias('jphooiveld_eventsauce.inflector', 'jphooiveld_eventsauce.inflector.dot_separated_snake_case');
};
