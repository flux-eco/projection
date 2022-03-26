<?php


namespace FluxEco\Projection\Adapters\Storage;

use FluxEco\Projection\Core\Domain;
use FluxEco\Projection\Core\Ports;
use FluxEco\Storage\Adapters\Api\StorageApi;

//TODO refactore this client and use a Core/Projection for mappings

class ProjectionStorageClient implements Ports\Storage\ProjectionStorageClient
{
    private const PROJECTION_ID_COLUMN_NAME = 'projectionId';


    private string $databaseName;

    private function __construct(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    final public static function new(string $databaseName): self
    {
        return new self($databaseName);
    }

    private function provideSubjectIdFilter(string $subjectId, string $projectionName): array
    {
        $subjectIdColumnName = $this->provideSubjectIdColumnName();
        return [
            $subjectIdColumnName => $subjectId
        ];
    }

    private function provideSubjectIdColumnName(): string
    {
        return 'rootObjectAggregateId';
    }

    final public function createProjectionStorage(string $projectionName, array $jsonSchema): void
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $jsonSchema);

        if (array_key_exists(self::PROJECTION_ID_COLUMN_NAME, $jsonSchema['properties']) === false) {
            throw new \Exception('A projection storage schema MUST contain a ' . self::PROJECTION_ID_COLUMN_NAME . ' column!');
        }
        $storageApi->createStorage(null, true);
    }


    public function deleteProjectionStorage(string $projectionName, array $jsonSchema) : void
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $jsonSchema);
        $storageApi->deleteStorage();
    }

    final public function countTotalRows(): int
    {
        //todo
        return 9999;
        //return $this->storageApi->countTotalRows([], 0);
    }


    final public function deleteProjectedRows(string $projectionName, array $jsonSchema, string $projectionId): void
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $jsonSchema);

        $storageApi->deleteData([
            'projectionId' => $projectionId
        ]);
    }


    /** @param Domain\ProjectedRow[] $recordedRows */
    final public function storeRecordedRows(string $projectionName, array $jsonSchema,  array $recordedRows): void
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $jsonSchema);

        foreach($recordedRows as $recordedRow) {

            $data = $recordedRow->toArray();

            $filter = [self::PROJECTION_ID_COLUMN_NAME => $data[self::PROJECTION_ID_COLUMN_NAME]];
            $storageApi->storeData($filter, $data);
        }


    }

    /**
     * @return Domain\ProjectedRow[]
     */
    final public function queryProjectionStorage(string $projectionName, array $schema, int $sequenceOffSet = 0, int $limit = 0): array
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $schema);

        $filter = [];
        if ($sequenceOffSet > 0) {
            //$filter = ['sequence >= ' . $sequenceOffSet];
        }
        $result = $storageApi->getData($filter, $limit);

        return ProjectedRowsAdapter::fromQueryResult($schema, $result)->toProjectedRows();
    }

    //todo merge this method with method abvoe - we should return raw values or json?
    final public function query(string $projectionName, array $schema, array $filter = [], int $sequenceOffSet = 0, int $limit = 0): array
    {
        $storageApi = StorageApi::new($this->databaseName, $projectionName, $schema);
        if ($sequenceOffSet > 0) {
            //$filter = ['sequence >= ' . $sequenceOffSet];
        }
        return $storageApi->getData($filter, $limit);
    }

}