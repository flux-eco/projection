<?php


namespace FluxEco\Projection\Adapters\Api;

use FluxEco\Projection\{Core\Domain};

class RootObjectMapping
{
    private string $aggregateName;
    private string $aggregateId;
    private array $properties;


    private function __construct(
        string $aggregateName,
        string $aggregateId,
        array  $properties
    )
    {
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
        $this->properties = $properties;
    }

    public static function new(
        string $aggregateName,
        string $aggregateId,
        array  $properties): self
    {
        return new self(
            $aggregateName,
            $aggregateId,
            $properties
        );
    }

    public static function fromDomain(
        Domain\Models\RootObjectMapping $aggregateRootMapping
    ): self
    {
        return new self(
            $aggregateRootMapping->getAggregateName(),
            $aggregateRootMapping->getAggregateId(),
            $aggregateRootMapping->getProperties()
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

    final public function getProperties(): array
    {
        return $this->properties;
    }


}