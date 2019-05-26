<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\Consumer;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class JphooiveldEventSauceExtensionTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testDefaultAliasses()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $loader->load([$config], $configuration);

        $this->assertEquals('jphooiveld_eventsauce.message_dispatcher.synchronous', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));
        $this->assertEquals('jphooiveld_eventsauce.clock.system', (string)$configuration->getAlias('jphooiveld_eventsauce.clock'));
        $this->assertEquals('jphooiveld_eventsauce.message_decorator.chain', (string)$configuration->getAlias('jphooiveld_eventsauce.message_decorator'));
        $this->assertEquals('jphooiveld_eventsauce.inflector.dot_separated_snake_case', (string)$configuration->getAlias('jphooiveld_eventsauce.inflector'));
        $this->assertEquals('jphooiveld_eventsauce.message_repository.doctrine', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        $this->assertEquals('jphooiveld_eventsauce.event_serializer.constructing', (string)$configuration->getAlias('jphooiveld_eventsauce.event_serializer'));
        $this->assertEquals('jphooiveld_eventsauce.message_serializer.constructing', (string)$configuration->getAlias('jphooiveld_eventsauce.message_serializer'));
        $this->assertEquals('jphooiveld_eventsauce.upcaster.delegating', (string)$configuration->getAlias('jphooiveld_eventsauce.upcaster'));
    }

    /**
     * @throws \Exception
     */
    public function testTimezone()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['time_of_recording']['timezone'] = 'Europe/Amsterdam';

        $loader->load([$config], $configuration);
        $this->assertEquals('Europe/Amsterdam', $configuration->getParameter('jphooiveld_eventsauce.time_of_recording.timezone'));
    }

    /**
     * @throws \Exception
     */
    public function testDispatcherSynchronous()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $loader->load([$config], $configuration);
        $this->assertEquals('jphooiveld_eventsauce.message_dispatcher.synchronous', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));
        $this->assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.message_dispatcher.messenger'));

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(Consumer::class, $autoConfiguration);

        $definition = $autoConfiguration[Consumer::class];

        $this->assertTrue($definition->hasTag('eventsauce.consumer'));
    }

    /**
     * @throws \Exception
     */
    public function testDispatcherMessenger()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['messenger']['enabled']     = true;
        $config['messenger']['service_bus'] = 'messenger.bus.foo';

        $loader->load([$config], $configuration);

        $this->assertEquals('jphooiveld_eventsauce.message_dispatcher.messenger', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(Consumer::class, $autoConfiguration);

        $definition = $autoConfiguration[Consumer::class];

        $this->assertTrue($definition->hasTag('messenger.message_handler'));

        $tag = $definition->getTag('messenger.message_handler');

        $this->assertArrayHasKey('bus', $tag[0]);
        $this->assertEquals('messenger.bus.foo', $tag[0]['bus']);
    }

    /**
     * @throws \Exception
     */
    public function testRepositoryWithoutDoctrine()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['service']             = 'foo';
        $config['message_repository']['doctrine']['enabled'] = false;

        $loader->load([$config], $configuration);

        $this->assertEquals('foo', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        $this->assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.message_repository.doctrine'));
        $this->assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.command.create_schema'));
    }

    /**
     * @throws \Exception
     */
    public function testRepositoryWitDoctrine()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['service']           = 'foo';
        $config['message_repository']['doctrine']['table'] = 'bar';

        $loader->load([$config], $configuration);

        $this->assertEquals('jphooiveld_eventsauce.message_repository.doctrine', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        $this->assertTrue($configuration->hasDefinition('jphooiveld_eventsauce.command.create_schema'));
        $this->assertEquals('bar', $configuration->getParameter('jphooiveld_eventsauce.repository.doctrine.table'));
    }

    /**
     * @throws \Exception
     */
    public function testAutoConfigureAggregatesOn()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $loader->load([$config], $configuration);

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(AggregateRoot::class, $autoConfiguration);

        $definition = $autoConfiguration[AggregateRoot::class];

        $this->assertTrue($definition->hasTag('eventsauce.aggregate_repository'));
    }

    /**
     * @throws \Exception
     */
    public function testAutoConfigureAggregatesOff()
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['autoconfigure_aggregates'] = false;

        $loader->load([$config], $configuration);

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        $this->assertArrayNotHasKey(AggregateRoot::class, $autoConfiguration);
    }

    private function getDefaultConfig()
    {
        $yaml = <<<EOF
time_of_recording:
    timezone: UTC
messenger:
    enabled: false
    service_bus: messenger.bus.events
message_repository:
    service: jphooiveld_eventsauce.message_repository.doctrine
    doctrine:
        enabled: true
        connection: doctrine.dbal.default_connection
        table: event
    autoconfigure_aggregates: true
EOF;

        return (new Parser())->parse($yaml);
    }
}