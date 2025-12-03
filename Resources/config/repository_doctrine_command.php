<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('jphooiveld_eventsauce.command.create_schema', \Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand::class)
        ->args([
            '',
            '%jphooiveld_eventsauce.repository.doctrine.table%',
        ])
        ->tag('console.command');
};
