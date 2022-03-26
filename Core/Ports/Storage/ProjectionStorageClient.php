<?php

namespace FluxEco\Projection\Core\Ports\Storage;

use FluxEco\Projection\Core\Domain;


interface ProjectionStorageClient
{
    public function createProjectionStorage(string $projectionName, array $jsonSchema): void;

    public function deleteProjectionStorage(string $projectionName, array $jsonSchema): void;

    /** @param Domain\ProjectedRow[] */
    public function storeRecordedRows(string $projectionName, array $jsonSchema, array $recordedRows): void;

    public function deleteProjectedRows(string $projectionName, array $jsonSchema,string $projectionId): void;

    /**
     * @return Domain\ProjectedRow[]
     **/
    public function queryProjectionStorage(string $projectionName, array $jsonSchema, int $sequenceOffSet = 0, int $limit = 0):  array;

    //todo merge this method with method abvoe - we should return raw values or json?
    public function query(string $projectionName, array $jsonSchema, array $filter, int $sequenceOffSet = 0, int $limit = 0): array;

}