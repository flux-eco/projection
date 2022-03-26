<?php


namespace FluxEco\Projection\Adapters\SchemaRegistry;

use FluxEco\Projection\{Core\Ports};

class ProjectionSchemaClient implements Ports\SchemaRegistry\ProjectionSchemaClient
{
    private ProjectionSchemaRegistry $schemaRegistry;

    private function __construct(ProjectionSchemaRegistry $schemaRegistry)
    {
        $this->schemaRegistry = $schemaRegistry;
    }

    final public static function new(array $schemaDirectories): self
    {
        $schemaRegistry = ProjectionSchemaRegistry::new($schemaDirectories);
        return new self($schemaRegistry);
    }

    final public function getProjectionSchemasForAggregate(string $aggregateName): array
    {
        return $this->schemaRegistry->getProjectionsForAggregate($aggregateName);
    }

    final public function getProjectionSchema(string $projectionName): array
    {
        return $this->schemaRegistry->getProjection($projectionName);
    }
}