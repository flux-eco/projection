<?php

namespace FluxEco\Projection\Core\Application\Processes;

use FluxEco\Projection\Core\{Domain};

/**
 * @author martin@fluxlabs.ch
 */
class RefreshProjectionsCommand
{
    private string $aggregateId;
    private string $aggregateName;
    private array $projectionSchemas;
    private Domain\Models\RowValues $items;

    private function __construct(
        string                  $aggregateId,
        string                  $aggregateName,
        array                   $projectionSchemas,
        Domain\Models\RowValues $items,
    )
    {
        $this->aggregateId = $aggregateId;
        $this->aggregateName = $aggregateName;
        $this->projectionSchemas = $projectionSchemas;
        $this->items = $items;
    }

    public static function new(
        string                  $aggregateId,
        string                  $aggregateName,
        array                   $projectionSchemas,
        Domain\Models\RowValues $items
    ): self
    {
        return new self(
            $aggregateId,
            $aggregateName,
            $projectionSchemas,
            $items
        );
    }

    final public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    final public function getAggregateName(): string
    {
        return $this->aggregateName;
    }

    final public function getProjectionSchemas(): array
    {
        return $this->projectionSchemas;
    }

    final public function getItems(): Domain\Models\RowValues
    {
        return $this->items;
    }
}
