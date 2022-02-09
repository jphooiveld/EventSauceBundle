<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle;

use EventSauce\EventSourcing\Message;

trait ConsumableTrait
{
    public function __invoke(Message $message): void
    {
        $this->handle($message);
    }

    public function handle(Message $message): void
    {
        $event      = $message->event();
        $classParts = explode('\\', get_class($event));
        $method     = 'apply' . end($classParts);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($event, $message);
    }
}