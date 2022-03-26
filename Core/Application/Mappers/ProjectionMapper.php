<?php

namespace FluxEco\Projection\Core\Application\Mappers;
use FluxEco\Projection\Core\Domain;

class ProjectionMapper
{
    protected static $instances = [];

    private string $projectionName;
    private string $projectionId;
    /** @var array AggregateRootMapper[] */
    private array $aggregateRootMappers = [];

    private function __construct(string $projectionName, string $projectionId)
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
    }

    public static function new(string $projectionName, string $projectionId): self
    {
        if (empty($instances[$projectionId])) {
            static::$instances[$projectionId] = new self($projectionName, $projectionId);
        }
        return static::$instances[$projectionId];
    }

    public function append(string           $aggregateName,
                           string           $aggregateId,
                           string           $propertyKey,
                           string|int|float|array $propertyValue): self
    {
        
        if (is_array($propertyValue)) {
            $value = json_encode($propertyValue);
        } else {
            $value = $propertyValue;
        }

        $this->aggregateRootMappers[$aggregateId] = AggregateRootMapper::new(
            $this->projectionName,
            $this->projectionId,
            $aggregateName,
            $aggregateId
        )->appendProperty($propertyKey, $value);

        return $this;
    }

    final public static function getInstances(): array
    {
        return self::$instances;
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getProjectionId(): string
    {
        return $this->projectionId;
    }

    /** Domain\Models\RootObjectMapping[] */
    final public function getRootObjectMappings(): array
    {

        $rootObjectMappings = [];
        foreach ($this->aggregateRootMappers as $aggregateId => $mapper) {
            $rootObjectMappings[] = Domain\Models\RootObjectMapping::new(
                $mapper->getProjectionName(),
                $mapper->getProjectionId(),
                $mapper->getAggregateName(),
                $mapper->getAggregateId(),
                $mapper->getProperties()
            );
        }

        return $rootObjectMappings;
    }




}