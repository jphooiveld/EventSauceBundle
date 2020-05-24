<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Consumer;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class TodoCreated implements SerializablePayload
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
     * @return self
     */
    public static function fromPayload(array $payload): SerializablePayload
    {
        $self       = new self();
        $self->id   = $payload['id'];
        $self->name = $payload['name'];

        return $self;
    }
}