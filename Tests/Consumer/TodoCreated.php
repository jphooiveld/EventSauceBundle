<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\Serialization\SerializableEvent;

class TodoCreated implements SerializableEvent
{
    /**
     * @var int ID
     */
    private $id;

    /**
     * @var string Name
     */
    private $name;

    /**
     * {@inheritDoc}
     */
    public function toPayload(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function fromPayload(array $payload): SerializableEvent
    {
        $self       = new self();
        $self->id   = $payload['id'];
        $self->name = $payload['name'];

        return $self;
    }
}