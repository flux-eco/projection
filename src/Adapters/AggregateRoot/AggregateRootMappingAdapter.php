<?php


namespace FluxEco\Projection\Adapters\AggregateRoot;

use FluxEco\Projection\{Core\Domain};

class AggregateRootMappingAdapter
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

    public static function fromDomain(
        Domain\Models\AggregateRootMapping $aggregateRootMapping
    ): self
    {
        return new self(
            $aggregateRootMapping->getProjectionName(),
            $aggregateRootMapping->getProjectionId(),
            $aggregateRootMapping->getAggregateName(),
            $aggregateRootMapping->getAggregateId(),
            $aggregateRootMapping->getExternalId()
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

    public function getProjectionName() : string
    {
        return $this->projectionName;
    }

    public function getProjectionId() : string
    {
        return $this->projectionId;
    }

    public function getExternalId() : ?string
    {
        return $this->externalId;
    }
}