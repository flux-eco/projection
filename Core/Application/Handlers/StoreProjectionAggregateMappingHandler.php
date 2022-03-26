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
        Ports\Storage\ProjectionStorageClient       $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient
    ): self
    {
        $aggregateRootMappingProjectionName = 'AggregateRootMapping';
        $aggregateRootMappingSchema = $projectionSchemaClient->getProjectionSchema('AggregateRootMapping');
        $projectionStream = Domain\ProjectionStream::new(
            $projectionStorageClient,
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