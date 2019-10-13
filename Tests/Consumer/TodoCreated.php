<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class TodoCreated implements SerializablePayload
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
    public static function fromPayload(array $payload): SerializablePayload
    {
        $self       = new self();
        $self->id   = $payload['id'];
        $self->name = $payload['name'];

        return $self;
    }
}