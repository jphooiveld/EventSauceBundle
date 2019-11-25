<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use EventSauce\EventSourcing\Message;
use Jphooiveld\Bundle\EventSauceBundle\MessengerDispatcher;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\TodoCreated;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class MessengerDispatcherTest
 * @package Jphooiveld\Bundle\EventSauceBundle\Tests
 * @covers \Jphooiveld\Bundle\EventSauceBundle\MessengerDispatcher
 */
final class MessengerDispatcherTest extends TestCase
{
    public function testDispatch()
    {
        $event1   = TodoCreated::fromPayload(['id' => 1, 'name' => 'foo']);
        $message1 = new Message($event1);
        $event2   = TodoCreated::fromPayload(['id' => 2, 'name' => 'bar']);
        $message2 = new Message($event2);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->exactly(2))->method('dispatch')->will($this->returnCallback(function ($message) use ($message1, $message2) {
            return new  Envelope($message === $message1 ? $message1 : $message2);
        }));

        $dispatcher = new MessengerDispatcher($bus);
        $dispatcher->dispatch($message1, $message2);
    }
}
