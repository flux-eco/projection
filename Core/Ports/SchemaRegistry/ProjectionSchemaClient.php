<?php

namespace FluxEco\Projection\Core\Ports\SchemaRegistry;

interface ProjectionSchemaClient
{
    public function getProjectionSchemasForAggregate(string $aggregateName): array;
    public function getProjectionSchema(string $projectionName): array;
}