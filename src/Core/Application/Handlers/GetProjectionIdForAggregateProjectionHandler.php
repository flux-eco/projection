<?php

namespace FluxEco\Projection\Core\Application\Handlers;
use FluxEco\Projection\Core\Ports;

class GetProjectionIdForAggregateProjectionHandler implements Handler
{
    private Ports\Storage\ProjectionStorageClient       $projectionStorageClient;
    private Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient;

    private function __construct(
        Ports\Storage\ProjectionStorageClient       $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient
    )
    {
        $this->projectionStorageClient = $projectionStorageClient;
        $this->projectionSchemaClient = $projectionSchemaClient;
    }

    public static function new(
        Ports\Storage\ProjectionStorageClient       $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient
    ): self
    {
        return new self($projectionStorageClient, $projectionSchemaClient);
    }

    public function handle(GetProjectionIdForAggregateProjectionCommand|Command $command): ?string {

        $mappingProjectionName = 'AggregateRootMapping';
        $schema = $this->projectionSchemaClient->getProjectionSchema($mappingProjectionName);

        $projectionName = $command->getProjectionName();
        $aggregateId =  $command->getAggregateId();

        $filter = [
            'projectionName' => $projectionName,
            'aggregateId' => $aggregateId
        ];

        $result = $this->projectionStorageClient->query($mappingProjectionName, $schema, $filter);

        if (count($result) > 1) {
            throw new \RuntimeException('Inconsistent Database: There are more than one projections ' . $projectionName . ' for the same aggregate: ' . $aggregateId);
        }

        if (count($result) === 1) {
            return $result[0]['projectionId'];
        }

        return null;

    }
}