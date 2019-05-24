<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use EventSauce\EventSourcing\Message;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\NotifyCreated;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\TodoCreated;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\TodoStorage;
use PHPUnit\Framework\TestCase;

class ConsumableTraitTest extends TestCase
{
    public function testHandle()
    {
        $event   = TodoCreated::fromPayload(['id' => 1, 'name' => 'foo']);
        $message = new Message($event);
        $storage = new TodoStorage();

        $consumer = new NotifyCreated($storage);
        $consumer->handle($message);

        $this->assertEquals(1, $storage->id);
        $this->assertEquals('foo', $storage->name);
    }

    public function testInvoke()
    {
        $event   = TodoCreated::fromPayload(['id' => 2, 'name' => 'bar']);
        $message = new Message($event);
        $storage = new TodoStorage();

        $consumer = new NotifyCreated($storage);
        $consumer($message);

        $this->assertEquals(2, $storage->id);
        $this->assertEquals('bar', $storage->name);
    }
}