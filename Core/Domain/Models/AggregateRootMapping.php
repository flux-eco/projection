<?php

namespace FluxEco\Projection\Core\Domain\Models;

class AggregateRootMapping implements \JsonSerializable
{

    private string $projectionName;
    private string $projectionId;
    private string $aggregateName;
    private string $aggregateId;
    private ?string $externalId;

    private function __construct(
        string  $projectionName,
        string  $projectionId,
        string  $aggregateName,
        string  $aggregateId,
        ?string $externalId
    )
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
        $this->externalId = $externalId;
    }

    public static function fromArray(
        array $data
    ): self
    {
        return new self(
            $data['projectionName'],
            $data['projectionId'],
            $data['aggregateName'],
            $data['aggregateId'],
            $data['externalId']
        );
    }

    public static function new(
        string  $projectionName,
        string  $projectionId,
        string  $aggregateName,
        string  $aggregateId,
        ?string $externalId = null
    ): self
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

    final public function getExternalId(): ?string
    {
        return $this->externalId;
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