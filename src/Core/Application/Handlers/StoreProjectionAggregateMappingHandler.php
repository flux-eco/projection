<?php

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Domain, Ports};

class StoreProjectionAggregateMappingHandler implements Handler
{
    private Domain\ProjectionStream $projectionStream;

    private function __construct(
        Domain\ProjectionStream $projectionStream
    )
    {
        $this->projectionStream = $projectionStream;
    }

    public static function new(
        Ports\Outbounds $outbounds
    ): self
    {
        $aggregateRootMappingProjectionName = $outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $aggregateRootMappingSchema = $outbounds->getProjectionSchema($aggregateRootMappingProjectionName);
        $projectionStream = Domain\ProjectionStream::new(
            $outbounds,
            $aggregateRootMappingProjectionName,
            $aggregateRootMappingSchema
        );

        return new self($projectionStream);
    }

    public function handle(StoreProjectionAggregateMappingCommand|Command $command): void
    {
        $aggregateRootMapping = Domain\Models\AggregateRootIdProjectionIdMapping::new(
            $command->getProjectionName(),
            $command->getProjectionId(),
            $command->getAggregateName(),
            $command->getAggregateId()
        );
        $this->projectionStream->projectData($command->getProjectionId(), $aggregateRootMapping->toArray());
    }
}