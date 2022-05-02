<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class GetMappingAggregateRootIdProjectionIdCommand implements Command
{
    private string $aggregateName;
    private string $aggregateRootId;
    private string $projectionName;

    private function __construct(string $aggregateName, string $aggregateRootId, string $projectionName)
    {
        $this->aggregateName = $aggregateName;
        $this->aggregateRootId  = $aggregateRootId;
        $this->projectionName  = $projectionName;
    }


    public static function new(string $aggregateName, string $aggregateRootId, string $projectionName): self
    {
        return new self($aggregateName, $aggregateRootId, $projectionName);
    }

    public function getAggregateName() : string
    {
        return $this->aggregateName;
    }


    public function getAggregateRootId() : string
    {
        return $this->aggregateRootId;
    }

    public function getProjectionName() : string
    {
        return $this->projectionName;
    }


    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}