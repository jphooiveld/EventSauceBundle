<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\DependencyInjection;

use EventSauce\Clock\Clock;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\Upcasting\Upcaster;
use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV3;
use EventSauce\MessageRepository\DoctrineV2MessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV2;
use Exception;
use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Application;

final class JphooiveldEventSauceExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
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
            if (!interface_exists(MessageBusInterface::class)) {
                throw new LogicException('Symfony messenger dispatcher cannot be enabled as the component is not installed. Try running "composer require symfony/messenger".');
            }

            $loader->load('dispatcher_messenger.xml');
            $container->setAlias('jphooiveld_eventsauce.message_dispatcher', 'jphooiveld_eventsauce.message_dispatcher.messenger');
            $container->registerForAutoconfiguration(MessageConsumer::class)->addTag('messenger.message_handler', ['bus' => $config['messenger']['service_bus']]);

            $definition = $container->getDefinition('jphooiveld_eventsauce.message_dispatcher.messenger');
            $definition->setArgument(0, new Reference($config['messenger']['service_bus']));
        } else {
            $container->registerForAutoconfiguration(MessageConsumer::class)->addTag('eventsauce.consumer');
        }

        $container->setAlias('jphooiveld_eventsauce.message_repository', $config['message_repository']['service']);

        if ($config['message_repository']['doctrine']['enabled'] === true) {
            if (!class_exists(DoctrineUuidV4MessageRepositoryV3::class) && !class_exists(DoctrineUuidV4MessageRepositoryV2::class)) {
                throw new LogicException('Doctrine message repository cannot be enabled as the Doctrine Message Repository is not installed. Try running "composer require eventsauce/message-repository-for-doctrine" for Doctrine DBAL version 3 or. "composer require eventsauce/message-repository-for-doctrine-v2" for Doctrine DBAL version 2');
            }

            $jsonOptions = array_reduce($config['message_repository']['doctrine']['json_encode_options'], static function($a, $b) { return $a | $b; }, 0);

            $loader->load('repository_doctrine_base.xml');

            if (class_exists(DoctrineUuidV4MessageRepositoryV3::class)) {
                $loader->load('repository_doctrine_v3.xml');
            } else {
                $loader->load('repository_doctrine_v2.xml');
            }

            $container->setAlias('jphooiveld_eventsauce.message_repository', 'jphooiveld_eventsauce.message_repository.doctrine');
            $container->setParameter('jphooiveld_eventsauce.repository.doctrine.table', $config['message_repository']['doctrine']['table']);
            $container->setParameter('jphooiveld_eventsauce.repository.doctrine.json_encode_options', $jsonOptions);

            if ($config['message_repository']['doctrine']['table_schema'] === 'legacy') {
                $container->setAlias('jphooiveld_eventsauce.table_schema', 'jphooiveld_eventsauce.table_schema.legacy');
            }

            if ($config['message_repository']['doctrine']['uuid_encoder'] === 'binary') {
                $container->setAlias('jphooiveld_eventsauce.uuid_encoder', 'jphooiveld_eventsauce.uuid_encoder.binary');
            }

            $definition = $container->getDefinition('jphooiveld_eventsauce.message_repository.doctrine');
            $definition->setArgument(0, new Reference($config['message_repository']['doctrine']['connection']));

            if (class_exists(Application::class)) {
                $loader->load('repository_doctrine_command.xml');
                $definition = $container->getDefinition('jphooiveld_eventsauce.command.create_schema');
                $definition->setArgument(0, new Reference($config['message_repository']['doctrine']['connection']));
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
        $container->registerForAutoconfiguration(Upcaster::class)->addTag('eventsauce.upcaster');
        $container->registerAliasForArgument('jphooiveld_eventsauce.clock', Clock::class, 'clock');

    }
}