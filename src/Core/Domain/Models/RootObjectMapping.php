<?php

namespace FluxEco\Projection\Core\Domain\Models;

class RootObjectMapping implements \JsonSerializable
{

    private string $projectionName;
    private string $projectionId;
    private string $aggregateName;
    private string $aggregateId;
    private array $properties;

    private function __construct(
        string $projectionName,
        string $projectionId,
        string $aggregateName,
        string $aggregateId,
        array $properties
    )
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
        $this->properties = $properties;
    }


    public static function new(
        string $projectionName,
        string $projectionId,
        string $aggregateName,
        string $aggregateId,
        array $properties
    ): self
    {
        return new self(
            $projectionName,
            $projectionId,
            $aggregateName,
            $aggregateId,
            $properties
        );
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getProjectionId(): string
    {
        return $this->projectionId;
    }

    final public function getAggregateName(): string
    {
        return $this->aggregateName;
    }

    final public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    final public function getProperties(): array
    {
        return $this->properties;
    }


    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}