<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\Upcasting\DelegatableUpcaster;
use Exception;
use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class JphooiveldEventSauceExtension extends Extension
{
    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader        = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader->load('dispatcher.xml');
        $loader->load('headers.xml');
        $loader->load('inflector.xml');
        $loader->load('aggregate_repository.xml');
        $loader->load('serializer.xml');
        $loader->load('upcasting.xml');

        if ($config['messenger']['enabled'] === true) {
            if (!interface_exists('Symfony\Component\Messenger\MessageBusInterface')) {
                throw new LogicException('Symfony messenger dispatcher cannot be enabled as the component is not installed. Try running "composer require symfony/messenger".');
            }

            $loader->load('dispatcher_messenger.xml');
            $container->setAlias('jphooiveld_eventsauce.message_dispatcher', 'jphooiveld_eventsauce.message_dispatcher.messenger');
            $container->registerForAutoconfiguration(Consumer::class)->addTag('messenger.message_handler', ['bus' => $config['messenger']['service_bus']]);

            $definition = $container->getDefinition('jphooiveld_eventsauce.message_dispatcher.messenger');
            $definition->setArgument(0, new Reference($config['messenger']['service_bus']));

        } else {
            $container->registerForAutoconfiguration(Consumer::class)->addTag('eventsauce.consumer');
        }

        $container->setAlias('jphooiveld_eventsauce.message_repository', $config['message_repository']['service']);

        if ($config['message_repository']['doctrine']['enabled'] === true) {
            $loader->load('repository_doctrine.xml');

            $container->setAlias('jphooiveld_eventsauce.message_repository', 'jphooiveld_eventsauce.message_repository.doctrine');
            $container->setParameter('jphooiveld_eventsauce.repository.doctrine.table', $config['message_repository']['doctrine']['table']);

            $definition = $container->getDefinition('jphooiveld_eventsauce.message_repository.doctrine');
            $definition->setArgument(0, new Reference($config['message_repository']['doctrine']['connection']));

            if (class_exists('Symfony\Component\Console\Application')) {
                $loader->load('repository_doctrine_command.xml');
                $definition = $container->getDefinition('jphooiveld_eventsauce.command.create_schema');
                $definition->setArgument(1, new Reference($config['message_repository']['doctrine']['connection']));
            }
        }

        if ($config['snapshot_repository']['enabled'] === true) {
            if ($config['snapshot_repository']['service'] === null) {
                throw new LogicException('You must set a valid snapshot service when snapshotting is enabled.');
            }

            $loader->load('snapshot_repository.xml');
            $container->setAlias('jphooiveld_eventsauce.snapshot_repository', $config['snapshot_repository']['service']);
        }

        $container->setParameter('jphooiveld_eventsauce.snapshot_repository.enabled', $config['snapshot_repository']['enabled']);
        $container->setParameter('jphooiveld_eventsauce.message_repository.aggregates', $config['message_repository']['aggregates']);
        $container->setParameter('jphooiveld_eventsauce.time_of_recording.timezone', $config['time_of_recording']['timezone']);
        $container->registerForAutoconfiguration(MessageDecorator::class)->addTag('eventsauce.message_decorator');
        $container->registerForAutoconfiguration(DelegatableUpcaster::class)->addTag('eventsauce.delegatable_upcaster');
    }
}