<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerDispatcher implements MessageDispatcher
{
    public function __construct(
        private MessageBusInterface $eventBus,
    ) {
    }

    public function dispatch(Message ...$messages): void
    {
        foreach ($messages as $message) {
            $this->eventBus->dispatch(new Envelope($message));
        }
    }
}