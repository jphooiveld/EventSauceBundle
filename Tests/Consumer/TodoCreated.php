<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class TodoCreated implements SerializablePayload
{
    private int $id;

    private string $name;

    /**
     * @return mixed[]
     */
    public function toPayload(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * @param mixed[] $payload
     */
    public static function fromPayload(array $payload): self
    {
        $self       = new self();
        $self->id   = $payload['id'];
        $self->name = $payload['name'];

        return $self;
    }
}