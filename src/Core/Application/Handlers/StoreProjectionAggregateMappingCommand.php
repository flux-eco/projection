<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class StoreProjectionAggregateMappingCommand implements Command
{
    private string $projectionName;
    private string $projectionId;
    private string $aggregateName;
    private string $aggregateId;

    private function __construct(string $projectionName, string $projectionId, string $aggregateName, string $aggregateId)
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
    }

    public static function new(string $projectionName, string $projectionId, string $aggregateName, string $aggregateId): self
    {
        return new self(
            $projectionName,
            $projectionId,
            $aggregateName,
            $aggregateId
        );
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getAggregateName(): string
    {
        return $this->aggregateName;
    }

    final public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    final public function getProjectionId(): string
    {
        return $this->projectionId;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}