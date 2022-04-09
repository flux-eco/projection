<?php

namespace FluxEco\Projection\Adapters;

use FluxEco\Projection\Core;

use fluxStorage;
use fluxValueObject;
use fluxGlobalStream;

class Outbounds implements Core\Ports\Outbounds
{
    private const PROJECTION_ID_COLUMN_NAME = 'projectionId';
    private const PROJECTION_NAME_AGGREGATE_ROOT_MAPPING = 'AggregateRootMapping';

    private string $projectionAppSchemaDirectory;
    private string $projectionEcoSchemaDirectory;
    private string $projectionStorageConfigEnvPrefix;

    private function __construct(
        string $projectionAppSchemaDirectory,
        string $projectionEcoSchemaDirectory,
        string $projectionStorageConfigEnvPrefix
    ) {
        $this->projectionAppSchemaDirectory = $projectionAppSchemaDirectory;
        $this->projectionEcoSchemaDirectory = $projectionEcoSchemaDirectory;
        $this->projectionStorageConfigEnvPrefix = $projectionStorageConfigEnvPrefix;
    }

    public static function new(
        string $projectionAppSchemaDirectory,
        string $projectionEcoSchemaDirectory,
        string $projectionStorageConfigEnvPrefix
    ) : self {
        return new self(
            $projectionAppSchemaDirectory,
            $projectionEcoSchemaDirectory,
            $projectionStorageConfigEnvPrefix
        );
    }

    public function getProjectionStorageConfigEnvPrefix() : string
    {
        return $this->projectionStorageConfigEnvPrefix;
    }

    final public function getProjectionSchemaDirectories() : array
    {
        return [
            $this->projectionAppSchemaDirectory,
            $this->projectionEcoSchemaDirectory
        ];
    }

    final public function getAggregateRootMappingProjectionName(): string {
        return self::PROJECTION_NAME_AGGREGATE_ROOT_MAPPING;
    }

    public function reprojectGlobalStreamStates(array $aggregateRootNames) : void
    {
        fluxGlobalStream\republishAllStates($aggregateRootNames);
    }

    public function createProjectionStorage(string $tableName, array $schema) : void
    {
        fluxStorage\createStorage($tableName, $schema, $this->projectionStorageConfigEnvPrefix);
    }

    public function deleteProjectionStorage(string $tableName, array $schema) : void
    {
        fluxStorage\deleteStorage($tableName, $schema, $this->projectionStorageConfigEnvPrefix);
    }

    final public function deleteProjectedRow(string $projectionName, array $jsonSchema, string $projectionId): void
    {
        $filter = ['projectionId' => $projectionId];
        fluxStorage\deleteData($projectionName, $jsonSchema,  $this->projectionStorageConfigEnvPrefix, $filter);
    }

    final public function countTotalProjectedRow(string $projectionName, array $jsonSchema, array $filter = [], $limit = 0): int
    {
        return fluxStorage\countTotalRows($projectionName, $jsonSchema,  $this->projectionStorageConfigEnvPrefix, $filter, $limit);
    }

    public function queryProjectionStorage(string $tableName, array $schema, array $filter, int $sequenceOffSet = 0, int $limit = 0): array
    {
        return fluxStorage\getData($tableName, $schema, $this->projectionStorageConfigEnvPrefix, $filter, $sequenceOffSet, $limit);
    }

    public function getProjectionSchema(string $projectionName) : array
    {
        $schemaRegistry = SchemaRegistry\ProjectionSchemaRegistry::new($this->getProjectionSchemaDirectories());
        return $schemaRegistry->getProjection($projectionName);
    }

    final public function getProjectionSchemasForAggregate(string $aggregateName): array
    {
        $schemaRegistry = SchemaRegistry\ProjectionSchemaRegistry::new($this->getProjectionSchemaDirectories());
        return $schemaRegistry->getProjectionsForAggregate($aggregateName);
    }

    /** @param Core\Domain\ProjectedRow[] $recordedRows */
    final public function storeProjectedRows(string $projectionName, array $jsonSchema,  array $recordedRows): void
    {
        foreach($recordedRows as $recordedRow) {
            $data = $recordedRow->toArray();
            $filter = [self::PROJECTION_ID_COLUMN_NAME => $data[self::PROJECTION_ID_COLUMN_NAME]];

            fluxStorage\storeData($projectionName, $jsonSchema, $this->projectionStorageConfigEnvPrefix, $filter, $data);
        }
    }

    final public function getNewUuid(): string
    {
        return fluxValueObject\getNewUuid();
    }

    final public function getCurrentTime(): string
    {
        return fluxValueObject\getCurrentTime();
    }
}