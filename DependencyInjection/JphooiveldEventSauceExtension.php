<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection;

use EventSauce\EventSourcing\AggregateRoot;
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
        $loader->load('repository.xml');
        $loader->load('serializer.xml');
        $loader->load('upcasting.xml');

        if ($config['message_dispatcher']['messenger']['enabled'] === true) {
            if (!interface_exists('Symfony\Component\Messenger\MessageBusInterface')) {
                throw new LogicException('Symfony messenger dispatcher cannot be enabled as the component is not installed. Try running "composer require symfony/messenger".');
            }

            $loader->load('dispatcher_messenger.xml');
            $container->setAlias('jphooiveld_eventsauce.message_dispatcher', 'jphooiveld_eventsauce.message_dispatcher.messenger');
            $container->registerForAutoconfiguration(Consumer::class)->addTag('messenger.message_handler', ['bus' => $config['message_dispatcher']['messenger']['service_bus']]);

            $definition = $container->getDefinition('jphooiveld_eventsauce.message_dispatcher.messenger');
            $definition->setArgument(0, new Reference($config['message_dispatcher']['messenger']['service_bus']));

        } else {
            $container->setAlias('jphooiveld_eventsauce.message_dispatcher', $config['message_dispatcher']['service']);
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

        $container->setParameter('jphooiveld_eventsauce.message_repository.autoconfigure_aggregates', $config['message_repository']['autoconfigure_aggregates']);

        if ($config['message_repository']['autoconfigure_aggregates'] === true) {
            $container->registerForAutoconfiguration(AggregateRoot::class)->addTag('eventsauce.aggregate_repository');
        }

        $container->setParameter('jphooiveld_eventsauce.time_of_recording.timezone', $config['time_of_recording']['timezone']);
        $container->registerForAutoconfiguration(MessageDecorator::class)->addTag('eventsauce.message_decorator');
        $container->registerForAutoconfiguration(DelegatableUpcaster::class)->addTag('eventsauce.delegatable_upcaster');
    }
}