<?php

namespace FluxEco\Projection\Adapters;

use FluxEco\Projection\Core;

use fluxStorage;
use fluxValueObject;
use fluxGlobalStream;
use fluxMessageServer;

class Outbounds implements Core\Ports\Outbounds
{
    private const PROJECTION_ID_COLUMN_NAME = 'projectionId';
    private const AGGREGATE_ID_COLUMN_NAME = 'aggregateId';
    private const PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_PROJECTION_ID = 'AggregateRootIdProjectionIdMapping';
    private const PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_EXTERNAL_ID = 'AggregateRootIdExternalIdMapping';

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

    public function startMessageServer() : void
    {
        fluxMessageServer\startServer('projection', MessageServer\MessageServerApi::new());
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

    final public function getProjectionNameMappingAggregateRootIdProjectionId(): string {
        return self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_PROJECTION_ID;
    }

    final public function getProjectionNameMappingAggregateRootIdExternalId(): string {
        return self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_EXTERNAL_ID;
    }


    public function reprojectGlobalStreamStates(array $aggregateRootNames) : void
    {
        fluxGlobalStream\republishAllStates($aggregateRootNames);
    }

    public function createProjectionStorage(string $tableName, array $schema) : void
    {
        if(array_key_exists('properties', $schema) === false) {
            return; //e.g. a pure select projection
        }
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

    public function queryProjectionStorage(string $projectionName, array $schema, ?array $filter = null, ?int $offset = null, ?int $limit = null, ?string $orderBy = null, ?string $search = null): array
    {
        return fluxStorage\getData($projectionName, $schema, $this->projectionStorageConfigEnvPrefix, $filter, $offset, $limit, $orderBy, $search);
    }

    public function getProjectionSchema(string $projectionName) : array
    {
        $schemaRegistry = SchemaRegistry\ProjectionSchemaRegistry::new($this->getProjectionSchemaDirectories());
        return $schemaRegistry->getProjection($projectionName);
    }

    final public function getProjectionSchemasForAggregate(string $aggregateName): ?array
    {
        $schemaRegistry = SchemaRegistry\ProjectionSchemaRegistry::new($this->getProjectionSchemaDirectories());

        if($schemaRegistry->hasAggregateProjections($aggregateName) === false) {
            return null;
        }

        return $schemaRegistry->getProjectionsForAggregate($aggregateName);
    }

    final public function storeAggregateRootIdProjectionIdMapping(Core\Domain\Models\AggregateRootIdProjectionIdMapping $mapping): void
    {
        $data = $mapping->toArray();
        $filter = [self::AGGREGATE_ID_COLUMN_NAME => $mapping->getAggregateId(), self::PROJECTION_ID_COLUMN_NAME => $mapping->getProjectionId()];
        $jsonSchema = $this->getProjectionSchema(self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_PROJECTION_ID);

        fluxStorage\storeData(self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_PROJECTION_ID, $jsonSchema, $this->projectionStorageConfigEnvPrefix, $filter, $data);
    }

    final public function storeAggregateRootIdExternalIdMapping(Core\Domain\Models\AggregateRootIdExternalIdMapping $mapping): void
    {
        $data = $mapping->toArray();
        $filter = [self::AGGREGATE_ID_COLUMN_NAME => $mapping->getAggregateId()];
        $jsonSchema = $this->getProjectionSchema(self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_EXTERNAL_ID);

        fluxStorage\storeData(self::PROJECTION_NAME_MAPPING_AGGREGATE_ROOT_ID_EXTERNAL_ID, $jsonSchema, $this->projectionStorageConfigEnvPrefix, $filter, $data);
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