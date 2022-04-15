<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class GetAggregateRootMappingForExternalIdCommand implements Command
{
    private string $projectionName;
    private string $aggregateName;
    private string $externalId;

    private function __construct(string $projectionName, string $aggregateName, string $externalId)
    {
        $this->projectionName = $projectionName;
        $this->aggregateName = $aggregateName;
        $this->externalId  = $externalId;
    }


    public static function new(string $projectionName, string $aggregateName, string $externalId): self
    {
        return new self($projectionName, $aggregateName, $externalId);
    }


    public function getProjectionName() : string
    {
        return $this->projectionName;
    }

    public function getAggregateName() : string
    {
        return $this->aggregateName;
    }

    public function getExternalId() : string
    {
        return $this->externalId;
    }


    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}