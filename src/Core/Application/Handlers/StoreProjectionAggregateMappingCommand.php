<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class StoreProjectionAggregateMappingCommand implements Command
{
    private string $projectionName;
    private string $projectionId;
    private string $aggregateName;
    private string $aggregateId;
    private ?string $externalId = null;

    private function __construct(string $projectionName, string $projectionId, string $aggregateName, string $aggregateId, ?string $externalId)
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
        $this->externalId = $externalId;
    }

    public static function new(string $projectionName, string $projectionId, string $aggregateName, string $aggregateId,  ?string $externalId): self
    {
        return new self(
            $projectionName,
            $projectionId,
            $aggregateName,
            $aggregateId,
            $externalId
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

    final public function getExternalId(): ?string
    {
        return $this->externalId;
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