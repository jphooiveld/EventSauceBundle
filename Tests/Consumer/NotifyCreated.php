<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\Consumer;
use Jphooiveld\Bundle\EventSauceBundle\ConsumableTrait;

final class NotifyCreated implements Consumer
{
    use ConsumableTrait;

    /**
     * @var TodoStorage
     */
    private $storage;

    /**
     * Constructor
     *
     * @param TodoStorage $storage
     */
    public function __construct(TodoStorage $storage)
    {
        $this->storage = $storage;
    }

    public function applyTodoCreated(TodoCreated $event): void
    {
        $payload             = $event->toPayload();
        $this->storage->id   = $payload['id'];
        $this->storage->name = $payload['name'];
    }
}