<?php

namespace FluxEco\Projection\Core\Application\Mappers;

use FluxEco\Projection\Core\Domain;

//todo refactor
class AggregateRootMapper implements \JsonSerializable
{
    /** @var AggregateRootMapper[] */
    protected static $instances = [];

    private string $projectionName;
    private string $projectionId;
    private string $aggregateName;
    private string $aggregateId;
    private array $properties = [];

    private function __construct(
        string $projectionName,
        string $projectionId,
        string $aggregateName,
        string $aggregateId,
    )
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->aggregateName = $aggregateName;
        $this->aggregateId = $aggregateId;
    }


    public static function new(
        string $projectionName,
        string $projectionId,
        string $aggregateName,
        string $aggregateId
    ): self
    {
        if (empty(static::$instances[$aggregateId])) {
            static::$instances[$aggregateId] = new self(
                $projectionName,
                $projectionId,
                $aggregateName,
                $aggregateId
            );
        }


        return static::$instances[$aggregateId];
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

    final public function appendProperty(string $propertyKey, string|int|float $propertyValue): self
    {
        //todo assert
        $this->properties[$propertyKey] = $propertyValue;
        return $this;
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