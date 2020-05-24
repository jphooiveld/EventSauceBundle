<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection;

use EventSauce\EventSourcing\Consumer;
use Exception;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Class JphooiveldEventSauceExtensionTest
 * @package Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension
 * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Configuration
 */
final class JphooiveldEventSauceExtensionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testDefaultAliasses(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $loader->load([$config], $configuration);

        self::assertSame('jphooiveld_eventsauce.message_dispatcher.synchronous', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));
        self::assertSame('jphooiveld_eventsauce.clock.system', (string)$configuration->getAlias('jphooiveld_eventsauce.clock'));
        self::assertSame('jphooiveld_eventsauce.message_decorator.chain', (string)$configuration->getAlias('jphooiveld_eventsauce.message_decorator'));
        self::assertSame('jphooiveld_eventsauce.inflector.dot_separated_snake_case', (string)$configuration->getAlias('jphooiveld_eventsauce.inflector'));
        self::assertSame('jphooiveld_eventsauce.message_repository.doctrine', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        self::assertSame('jphooiveld_eventsauce.payload_serializer.constructing', (string)$configuration->getAlias('jphooiveld_eventsauce.payload_serializer'));
        self::assertSame('jphooiveld_eventsauce.message_serializer.constructing', (string)$configuration->getAlias('jphooiveld_eventsauce.message_serializer'));
        self::assertSame('jphooiveld_eventsauce.upcaster.delegating', (string)$configuration->getAlias('jphooiveld_eventsauce.upcaster'));
    }

    /**
     * @throws Exception
     */
    public function testTimezone(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['time_of_recording']['timezone'] = 'Europe/Amsterdam';

        $loader->load([$config], $configuration);
        self::assertSame('Europe/Amsterdam', $configuration->getParameter('jphooiveld_eventsauce.time_of_recording.timezone'));
    }

    /**
     * @throws Exception
     */
    public function testDispatcherSynchronous(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $loader->load([$config], $configuration);
        self::assertSame('jphooiveld_eventsauce.message_dispatcher.synchronous', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));
        self::assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.message_dispatcher.messenger'));

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        self::assertArrayHasKey(Consumer::class, $autoConfiguration);

        $definition = $autoConfiguration[Consumer::class];

        self::assertTrue($definition->hasTag('eventsauce.consumer'));
    }

    /**
     * @throws Exception
     */
    public function testDispatcherMessenger(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['messenger']['enabled']     = true;
        $config['messenger']['service_bus'] = 'messenger.bus.foo';

        $loader->load([$config], $configuration);

        self::assertSame('jphooiveld_eventsauce.message_dispatcher.messenger', (string)$configuration->getAlias('jphooiveld_eventsauce.message_dispatcher'));

        $autoConfiguration = $configuration->getAutoconfiguredInstanceof();

        self::assertArrayHasKey(Consumer::class, $autoConfiguration);

        $definition = $autoConfiguration[Consumer::class];

        self::assertTrue($definition->hasTag('messenger.message_handler'));

        $tag = $definition->getTag('messenger.message_handler');

        self::assertArrayHasKey('bus', $tag[0]);
        self::assertSame('messenger.bus.foo', $tag[0]['bus']);
    }

    /**
     * @throws Exception
     */
    public function testRepositoryWithoutDoctrine(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['service']             = 'foo';
        $config['message_repository']['doctrine']['enabled'] = false;

        $loader->load([$config], $configuration);

        self::assertSame('foo', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        self::assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.message_repository.doctrine'));
        self::assertFalse($configuration->hasDefinition('jphooiveld_eventsauce.command.create_schema'));
    }

    /**
     * @throws Exception
     */
    public function testRepositoryWithDoctrine(): void
    {
        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['service']           = 'foo';
        $config['message_repository']['doctrine']['table'] = 'bar';

        $loader->load([$config], $configuration);

        self::assertSame('jphooiveld_eventsauce.message_repository.doctrine', (string)$configuration->getAlias('jphooiveld_eventsauce.message_repository'));
        self::assertTrue($configuration->hasDefinition('jphooiveld_eventsauce.command.create_schema'));
        self::assertSame('bar', $configuration->getParameter('jphooiveld_eventsauce.repository.doctrine.table'));
        self::assertSame(JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION, $configuration->getParameter('jphooiveld_eventsauce.repository.doctrine.json_options'));
    }

    /**
     * @throws Exception
     */
    public function testInvalidJsonOptions(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $configuration = new ContainerBuilder();
        $loader        = new JphooiveldEventSauceExtension();
        $config        = $this->getDefaultConfig();

        $config['message_repository']['doctrine']['json_options'][] = 3;

        $loader->load([$config], $configuration);
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
        json_options:
            - !php/const JSON_PRETTY_PRINT
            - !php/const JSON_PRESERVE_ZERO_FRACTION
    aggregates:
        - 'Jphooiveld\Bundle\EventSauceBundle\Tests\Aggregate\Order'
EOF;

        return (new Parser())->parse($yaml, Yaml::PARSE_CONSTANT);
    }
}
