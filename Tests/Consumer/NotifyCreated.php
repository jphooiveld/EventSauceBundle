<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\MessageConsumer;
use Jphooiveld\Bundle\EventSauceBundle\ConsumableTrait;

final class NotifyCreated implements MessageConsumer
{
    use ConsumableTrait;

    public function __construct(
        private TodoStorage $storage,
    ) {
    }

    private function applyTodoCreated(TodoCreated $event): void
    {
        $payload             = $event->toPayload();
        $this->storage->id   = $payload['id'];
        $this->storage->name = $payload['name'];
    }
}