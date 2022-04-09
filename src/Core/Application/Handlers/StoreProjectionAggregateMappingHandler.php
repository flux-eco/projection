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
        $aggregateRootMappingProjectionName = 'AggregateRootMapping';
        $aggregateRootMappingSchema = $outbounds->getProjectionSchema('AggregateRootMapping');
        $projectionStream = Domain\ProjectionStream::new(
            $outbounds,
            $aggregateRootMappingProjectionName,
            $aggregateRootMappingSchema
        );

        return new self($projectionStream);
    }

    public function handle(StoreProjectionAggregateMappingCommand|Command $command): void
    {
        $aggregateRootMapping = Domain\Models\AggregateRootMapping::new(
            $command->getProjectionName(),
            $command->getProjectionId(),
            $command->getAggregateName(),
            $command->getAggregateId(),
            $command->getExternalId()
        );
        $this->projectionStream->projectData($command->getProjectionId(), $aggregateRootMapping->toArray());
    }
}