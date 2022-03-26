<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class GetProjectionIdForAggregateProjectionCommand implements Command
{
    private string $projectionName;
    private string $aggregateId;

    private function __construct(
        string $projectionName,
        string $aggregateId
    )
    {
        $this->projectionName = $projectionName;
        $this->aggregateId = $aggregateId;
    }

    public static function new(
        string $projectionName,
        string $aggregateId
    ): self
    {
        return new self($projectionName, $aggregateId);
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}