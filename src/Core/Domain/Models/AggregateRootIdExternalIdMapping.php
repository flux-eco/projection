<?php

namespace FluxEco\Projection\Core\Domain\Models;

class AggregateRootIdExternalIdMapping implements \JsonSerializable
{
    private string $aggregateName;
    private string $aggregateId;
    private ?string $externalId;
    private ?string $externalSource;

    private function __construct(
        string  $aggregateName,
        string  $aggregateId,
        ?string $externalId,
        ?string $externalSource
    )
    {
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
        $this->externalId = $externalId;
        $this->externalSource = $externalSource;
    }

    public static function fromArray(
        array $data
    ): self
    {
        return new self(
            $data['aggregateName'],
            $data['aggregateId'],
            $data['externalId'],
            $data['externalSource']
        );
    }

    public static function new(
        string  $aggregateName,
        string  $aggregateId,
        ?string $externalId = null,
        ?string $externalSource = null,
    ): self
    {
        return new self(
            $aggregateName,
            $aggregateId,
            $externalId,
            $externalSource
        );
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

    public function getExternalSource() : ?string
    {
        return $this->externalSource;
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