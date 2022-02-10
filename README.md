# JphooiveldEventSauceBundle
![BUild status](https://github.com/jphooiveld/EventSauceBundle/actions/workflows/ci.yaml/badge.svg)
## Info

This bundle integrates EventSauce, and it's doctrine message repository into your symfony application.

It's strongly advised to know how EventSauce works or read up on the offical website (https://eventsauce.io) 
before you install this bundle.

## Installation

Install the bundle into your symfony project.

    $ composer require jphooiveld/eventsauce-bundle

Installing the bundle will give you an error because doctrine message repository is missing.
You must install the doctrine message repository fromt the EventSauce project.
Since the EventSauce project has a version for both Doctrine DBAL version 2.x and 3.x, it's impossible to use
both at the same time, so you need to chooce the correct version yourself. The bundle will automatically
check which version of the message repository is installed and load the appropriate services.

If you use a project Doctrine DBAL version 3.x. you must install the package.

    $ composer require eventsauce/message-repository-for-doctrine

Or if you use a project with Doctrine DBAL version 2.x. you must install the package.

    $ composer require eventsauce/message-repository-for-doctrine-v2

    
If you want to use symfony messenger as dispatcher instead of the default dispatcher you must install 
the package.

    $ composer require symfony/messenger


If you want to use the console command to generate the database table for you, you must install the 
package.

    $ composer require symfony/console

Normally symfony should be able to register the bundle automatically. If not you will need to add it 
to the bundles.php in your config directory.

```php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Jphooiveld\Bundle\EventSauceBundle\JphooiveldEventSauceBundle::class => ['all' => true],
];
```

## Configuration

```yaml
jphooiveld_event_sauce:
    time_of_recording:
        # The timezone to use for recorded messages, defaults to PHP ini setting.
        timezone: UTC
    messenger:
        # Use symfony messenger instead of EventSauce's default dispatcher (needs package symfony/messenger installed).
        enabled: false
        # The name of the messenger command bus service to use.
        service_bus: messenger.bus.events
    message_repository:
        # Override if you don't want to use the default doctrine message repository but your own implementation. 
        # If doctrine is enabled this will be ignored.
        service: jphooiveld_eventsauce.message_repository.doctrine
        doctrine:
            # Use doctrine as default message repository. If you turn this off you must provide your own service.
            enabled: true
            # Service that implements a doctrine connection. We assume doctrine bundle default here.
            connection: doctrine.dbal.default_connection
            # The table name in the database to store the messages.
            table: event
            # JSON encode options for the doctrine message repository
            json_encode_options:
                - !php/const JSON_PRETTY_PRINT
                - !php/const JSON_PRESERVE_ZERO_FRACTION
            # Table schema to use: default (EventSauce 1.x) or legacy (EventSauce 0.8.x)  
            table_schema: default
            # UUID encpder to use: string or binary   
            uuid_encoder: string
        # Configure provided aggregate roots to use the default repository implementations as created by the bundle 
        aggregates:
            - App\Domain\Order
    snapshot_repository:
        # User snapshotting
        enabled: false
        # Service must point to a valid class that implements EventSauce\EventSourcing\Snapshotting\SnapshotRepository
        service: 'App\Infrastructure\MySnapshotRepository'
            
```

## Configuration Detail

### Messenger

By default, the synchronous message dispatcher from EventSauce is used. If you enable symfony messenger and 
provide the service id of the event bus all messages will be handled by the messenger component. 

### Message repository

The doctrine message component will automatically be installed as a dependency, otherwise there would be a 
non-functioning application. But if you want to install another type of repository or create your own it's 
easy to reference it in the service parameter. If doctrine is enabled (and it's by default) this will be
ignored.

Configuring aggregates is an easy way to bind your aggregates to the message repository with
default EventSauce services. A compiler pass creates the services automatically internally.

Lets for example assume you have an aggregate root called Order
 
```php
<?php

namespace App\Domain;

use EventSauce\EventSourcing\AggregateRoot;

class Order implements AggregateRoot
{    
    // code
}
```

If you add 'App\Domain\Order' to the list of aggregates in the configuration the compiler pass will 
automatically create a service called **jphooiveld_eventsauce.aggregate_repository.order**. It will also
create an argument binding named **$orderRepository** for interface **EventSauce\EventSourcing\AggregateRootRepository** for this repository so that you can inject it into your own services 

Let's say we have a command handler that is responsible for persisting the order aggregate. We can now inject the 
order repository automatically for further use.

```php
<?php

namespace App\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;

class AddOrderHandler
{
    public function __construct(
        private AggregateRootRepository $orderRepository,
    ) {
    }
    
    // other code
}
```

If you don't want to configure your repository then don't add it to the list and configue it yourself.

When snapshotting is enabled, and the snapshotting service points to a valid instance of 
**EventSauce\EventSourcing\Snapshotting\SnapshotRepository** every provided aggregate (under aggregates in message_repository configuration) 
that implements  **EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting** will automatically configure the
message repository as well as the snapshot repository.

## Auto configuration

EventSauce has a number of interfaces that are autoconfigured when this bundle is installed to make your life a lot easier.

### Consumers

Consumers are responsible for handling events from the aggregates. The message dispatcher is responsible for delegating the
events to the consumers. Every class you create and implements **EventSauce\EventSourcing\MessageConsumer** will automatically 
receive events from the message dispatcher. However, if you intend to use Symfony messenger you must implement the __invoke
method. To overcome this limitation you can use the **ConsumableTrait** provided in the bundle. This will make sure it will work
with the default message dispatcher from EventSauce as wel as the symfony messenger component. The trait will also let consumer's
handler the events the same way as the aggregate repository does. It will look for methods that start with apply and after that the
name of the event. Let's say we have a **OrderCreated** class which is an event that indicated ther order was created. Now we 
want to send an email notifcation. For example, we can create a listener as follows:

```php
<?php

namespace App\Service;

use App\Event\OrderCreated;
use EventSauce\EventSourcing\MessageConsumer;
use Jphooiveld\Bundle\EventSauceBundle\ConsumableTrait;
use Symfony\Component\Mailer\MailerInterface
use Symfony\Component\Mime\RawMessage;

class SendMailNotification implements MessageConsumer
{
    use ConsumableTrait;
        
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }
        
    protected function applyOrderCreated(OrderCreated $event): void
    {
        $message = new RawMessage(); 

        // other code
          
        $this->mailer->send($message);
    }
}
```

Whenever an order is created the method applyOrderCreated will be called.

### Message decorators

Message decorators allow you to add extra headers to a message. Every class you create and implements 
**EventSauce\EventSourcing\MessageDecorator** will automatically add the headers that you define to the persisted message.

### Upcasting

Upcasters can transform messages in case events change. Every class you create and implements 
**EventSauce\EventSourcing\Upcasting\Upcaster** will automatically be used.

## Overriding default services

All services used in the bundles are actually aliases to real implementations. If you want to override services all you 
have to do is create your own services and override the aliases in the build method in your Kernel. 

Beware that if you start to override services stuff can and will break because autoconfiguration uses a lot of the default
implementations.

| Alias                                      | Interface                                                | Breaks auto configuration |
|--------------------------------------------|----------------------------------------------------------| --------------------------|
| jphooiveld_eventsauce.clock                | EventSauce\Clock\Clock                                   | no                        |
| jphooiveld_eventsauce.payload_serializer   | EventSauce\EventSourcing\Serialization\PayloadSerializer | no                        |
| jphooiveld_eventsauce.message_serializer   | EventSauce\EventSourcing\Serialization\MessageSerializer | no                        |
| jphooiveld_eventsauce.upcaster             | EventSauce\EventSourcing\Upcasting\Upcaster              | yes                       |
| jphooiveld_eventsauce.inflector            | EventSauce\EventSourcing\ClassNameInflector              | no                        |
| jphooiveld_eventsauce.message_decorator    | EventSauce\EventSourcing\MessageDecorator                | yes                       |
| jphooiveld_eventsauce.message_dispatcher   | EventSauce\EventSourcing\MessageDispatcher               | yes                       |

Let's say you want to create your own class inflector called **App\EventSourcing\UnderscoreInflector** 
that implements interface  **EventSauce\EventSourcing\ClassNameInflector**. The only thing you need to do is set the 
alias in your kernel's build method and other dependent services will automatically use it.

```php
<?php

namespace App;

use App\EventSourcing\UnderscoreInflector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    protected function build(ContainerBuilder $container)
    {
        $container->setAlias('jphooiveld_eventsauce.inflector', UnderscoreInflector::class);
    }
    
    // other code
}
```

# Upgrading from version 1.x

You must install the correct Doctrine Message Repository package from the EventSauce project when 
upgrading to version 2.x of this bundle.

A new option called **table_schema** has been added under the **message_repository** configuration.
If you want to maintain backwards compatibility with your current database you will need to set it to **legacy**.

You can remove any bindings for aggregate repositories from your service configuration as the bundle
already configures that for you in the compiler pass through the method **registerAliasForArgument**.
See example below for our Order aggregate.

```yaml
services:
    _defaults:
        bind:
            # Line below here is no longer needed, compiler pass will take care of it
            # EventSauce\EventSourcing\AggregateRootRepository $orderRepository: '@jphooiveld_eventsauce.aggregate_repository.order'

```

Some service configuration has changed. So if you have manually overriden anything you must check that configuration.

For upgrading your code EventSauce provides a rector package. See https://github.com/EventSaucePHP/RectorFrom0to1. 
Learn more about Rector at https://github.com/rectorphp/rector 


# License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE).
