<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use EventSauce\EventSourcing\Message;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\NotifyCreated;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\TodoCreated;
use Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer\TodoStorage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jphooiveld\Bundle\EventSauceBundle\ConsumableTrait
 */
final class ConsumableTraitTest extends TestCase
{
    public function test_handle(): void
    {
        $event   = TodoCreated::fromPayload(['id' => 1, 'name' => 'foo']);
        $message = new Message($event);
        $storage = new TodoStorage();

        $consumer = new NotifyCreated($storage);
        $consumer->handle($message);

        self::assertSame(1, $storage->id);
        self::assertSame('foo', $storage->name);
    }

    public function test_invoke(): void
    {
        $event   = TodoCreated::fromPayload(['id' => 2, 'name' => 'bar']);
        $message = new Message($event);
        $storage = new TodoStorage();

        $consumer = new NotifyCreated($storage);
        $consumer($message);

        self::assertSame(2, $storage->id);
        self::assertSame('bar', $storage->name);
    }
}
