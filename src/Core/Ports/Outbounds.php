<?php

namespace FluxEco\Projection\Core\Ports;
use FluxEco\Projection\{Core\Domain};

interface Outbounds
{
    public function startMessageServer(): void;
    public function reprojectGlobalStreamStates(array $aggregateRootNames): void;
    public function getProjectionSchemaDirectories(): array;
    public function getProjectionNameMappingAggregateRootIdProjectionId(): string;
    public function getProjectionNameMappingAggregateRootIdExternalId(): string;
    public function createProjectionStorage(string $tableName, array $schema);
    public function deleteProjectedRow(string $projectionName, array $jsonSchema, string $projectionId): void;
    public function countTotalProjectedRow(string $projectionName, array $jsonSchema, array $filter = [], $limit = 0): int;
    public function deleteProjectionStorage(string $tableName, array $schema);
    public function queryProjectionStorage(string $projectionName, array $schema, ?array $filter = null, ?int $offset = null, ?int $limit = null, ?string $orderBy = null, ?string $search = null): array;
    public function getProjectionSchema(string $projectionName) : array;
    public function storeAggregateRootIdProjectionIdMapping(Domain\Models\AggregateRootIdProjectionIdMapping $mapping): void;
    public function storeAggregateRootIdExternalIdMapping(Domain\Models\AggregateRootIdExternalIdMapping $mapping): void;
    public function storeProjectedRows(string $projectionName, array $jsonSchema,  array $recordedRows): void;
    public function getNewUuid(): string;
    public function getCurrentTime(): string;
}