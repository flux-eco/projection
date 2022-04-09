<?php

namespace FluxEco\Projection\Core\Ports;

interface Outbounds
{
    public function reprojectGlobalStreamStates(array $aggregateRootNames): void;
    public function getProjectionSchemaDirectories(): array;
    public function getAggregateRootMappingProjectionName(): string;
    public function createProjectionStorage(string $tableName, array $schema);
    public function deleteProjectedRow(string $projectionName, array $jsonSchema, string $projectionId): void;
    public function countTotalProjectedRow(string $projectionName, array $jsonSchema, array $filter = [], $limit = 0): int;
    public function deleteProjectionStorage(string $tableName, array $schema);
    public function queryProjectionStorage(string $projectionName, array $schema, array $filter, int $sequenceOffSet = 0, int $limit = 0): array;
    public function getProjectionSchema(string $projectionName) : array;
    public function storeProjectedRows(string $projectionName, array $jsonSchema,  array $recordedRows): void;
    public function getNewUuid(): string;
    public function getCurrentTime(): string;
}