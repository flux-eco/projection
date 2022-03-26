<?php

namespace FluxEco\Projection\Core\Ports;

use Exception;
use FluxEco\Projection\Adapters\Api\RootObjectMapping;
use FluxEco\Projection\Core\{Application,
    Application\Handlers,
    Application\Processes\RefreshProjectionsCommand,
    Application\Processes\RefreshProjectionsProcess,
    Domain
};

//todo

class ProjectionService
{
    private Storage\ProjectionStorageClient $projectionStorageClient;
    private SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient;
    private ValueObjectProvider\ValueObjectProviderClient $valueObjectProviderClient;

    private function __construct(
        Storage\ProjectionStorageClient $projectionStorageClient,
        SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient,
        ValueObjectProvider\ValueObjectProviderClient $valueObjectProviderClient,

    ) {
        $this->projectionStorageClient = $projectionStorageClient;
        $this->projectionSchemaClient = $projectionSchemaClient;
        $this->valueObjectProviderClient = $valueObjectProviderClient;
    }

    public static function new(Configs\ProjectionOutbounds $projectionOutbounds) : self
    {
        return new self(
            $projectionOutbounds->getProjectionStorageClient(),
            $projectionOutbounds->getProjectionSchemaClient(),
            $projectionOutbounds->getValueObjectProvider()
        );
    }

    final public function initalizeProjectionStorage(
        array $projectionSchema
    ) : void {
        $projectionName = $projectionSchema['name'];
        $projectionStorageClient = $this->projectionStorageClient;

        $deleteProjectionStorage = Handlers\DeleteProjectionStorageCommand::new($projectionName, $projectionSchema);
        $deleteProjectionStorageHandler = Handlers\DeleteProjectionStorageHandler::new($projectionStorageClient);
        $deleteProjectionStorageHandler->handle($deleteProjectionStorage);


        $createProjectionStorage = Handlers\CreateProjectionStorageCommand::new($projectionName, $projectionSchema);
        $createProjectionStorageHandler = Handlers\CreateProjectionStorageHandler::new($projectionStorageClient);
        $createProjectionStorageHandler->handle($createProjectionStorage);


    }

    final public function deleteProjectedRows(
        string $subjectId,
        string $subjectName
    ) {
        $aggregateRootMappings = $this->getAggregateRootMappingsForAggregateId($subjectId);

        if ($aggregateRootMappings !== null) {
            $schemaRegistryClient = $this->projectionSchemaClient;
            foreach ($aggregateRootMappings as $mapping) {
                $schema = $schemaRegistryClient->getProjectionSchema($mapping->getProjectionName());
                $this->projectionStorageClient->deleteProjectedRows(
                    $mapping->getProjectionName(),
                    $schema,
                    $mapping->getProjectionId()
                );
            }
        }
    }

    /**
     * @param Domain\Models\StateValue[] $values
     */
    final public function onReceiveAggregateRootChangedEvent(
        string $aggregateId,
        string $aggregateName,
        Domain\Models\RowValues $values

    ) : void {
        $projectionStorageClient = $this->projectionStorageClient;
        $projectionSchemaClient = $this->projectionSchemaClient;
        $valueObjectProviderClient = $this->valueObjectProviderClient;

        $projectionSchemas = $this->projectionSchemaClient->getProjectionSchemasForAggregate($aggregateName);

        $command = RefreshProjectionsCommand::new(
            $aggregateId,
            $aggregateName,
            $projectionSchemas,
            $values
        );
        RefreshProjectionsProcess::new($projectionStorageClient, $projectionSchemaClient,
            $valueObjectProviderClient)->handle($command);
    }

    /** @return Domain\Models\RootObjectMapping[] */
    final public function getAggregateRootMappingsForAggregateId(string $aggregateId) : ?array
    {
        $mappingProjectionName = 'AggregateRootMapping';
        $schema = $this->getProjectionSchema($mappingProjectionName);
        $filter = [
            'aggregateId' => $aggregateId
        ];

        $results = $this->projectionStorageClient->query($mappingProjectionName, $schema, $filter);
        print_r($results);
        $mappings = [];
        if ($results > 0) {
            foreach ($results as $result) {
                $mappings[] = Domain\Models\RootObjectMapping::new(
                    $result['projectionName'],
                    $result['projectionId'],
                    $result['aggregateName'],
                    $result['aggregateId'],
                    []
                );
            }
            return $mappings;
        }
        return null;

    }

    /*
    final public function getProjectionIdForAggregate(string $projectionName, string $aggregateName, string $aggregateId, ?string $externalId = null): string
    {
        $projectionId = $this->getProjectionIdForAggregateProjection($projectionName, $aggregateId);

        if ($projectionId === null) {
            $projectionId = $this->valueObjectProviderClient->createUuid();
            $aggregateRootMapping = Domain\Models\AggregateRootMapping::new(
                $projectionName,
                $projectionId,
                $aggregateName,
                $aggregateId,
                $externalId
            );
            $this->createItem($projectionName, $projectionId, $aggregateRootMapping->toArray());
            return $projectionId;
        }

        return $projectionId;
    }*/

    /*
    private function getProjectionIdForAggregateProjection(string $projectionName, string $aggregateId): ?string
    {
        $mappingProjectionName = 'AggregateRootMapping';
        $schema = $this->getProjectionSchema($mappingProjectionName);
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
    }*/

    /** @return ?Domain\Models\AggregateRootMapping[] */
    final public function getAggregateRootMappingsForProjectionId(string $projectionId) : ?array
    {
        $projectionName = 'AggregateRootMapping';
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $filter = ['projectionId' => $projectionId];
        $result = $this->projectionStorageClient->query($projectionName, $projectionSchema, $filter);
        if ($result > 0) {
            $aggregateRootMappings = [];
            foreach ($result as $key => $value) {
                $aggregateRootMappings[] = RootObjectMapping::new(
                    $value['aggregateName'],
                    $value['aggregateId'],
                    []
                );
            }
            return $aggregateRootMappings;
        }
        return null;
    }

    final public function getProjectionIdForExternalIdIfExists(string $projectionName, string $externalId) : ?string
    {

        $mappingProjectionName = 'AggregateRootMapping';
        $schema = $this->getProjectionSchema($mappingProjectionName);
        $filter = [
            'projectionName' => $projectionName,
            'externalId' => $externalId
        ];

        $results = $this->projectionStorageClient->query($mappingProjectionName, $schema, $filter);
        if (count($results) > 1) {
            throw new Exception('More than one mapping result found for external Id: ' . $externalId);
        }

        if (count($results) === 1) {
            return $results[0]['projectionId'];
        }
        return null;
    }

    /** @return Domain\Models\RootObjectMapping[] */
    final public function getAggregateRootMappings(
        string $projectionName,
        string $projectionId,
        array $data,
        ?string $externalId = null
    ) : array {
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $projectionMapper = Application\Mappers\ProjectionMapper::new($projectionName, $projectionId);

        //

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                //todo assert key exists as property
                $propertyPath = $projectionSchema['properties'][$key]['index'];
                $propertyPathParts = explode('.', $propertyPath);
                if ($propertyPathParts[1] === 'rootObject') {
                    $aggregateRootPropertyKey = $propertyPathParts[2];
                    $aggregateName = $propertyPathParts[0];

                    $mappingRow = $this->getItem('AggregateRootMapping', $projectionId);
                    if (empty($mappingRow['aggregateId'])) {
                        $aggregateId = $this->valueObjectProviderClient->createUuid();

                        $aggregateRootMapping = Domain\Models\AggregateRootMapping::new(
                            $projectionName,
                            $projectionId,
                            $aggregateName,
                            $aggregateId,
                            $externalId
                        );

                        //todo
                        $mappingProjectionName = 'AggregateRootMapping';
                        $this->createItem($mappingProjectionName, $projectionId, $aggregateRootMapping->toArray());
                    } else {
                        $aggregateRootMapping = Domain\Models\AggregateRootMapping::fromArray($mappingRow);
                    }

                    if ($value !== null) {
                        $projectionMapper->append(
                            $aggregateRootMapping->getAggregateName(),
                            $aggregateRootMapping->getAggregateId(),
                            $aggregateRootPropertyKey,
                            $value
                        );
                    }

                }
            }
        }

        return $projectionMapper->getRootObjectMappings();
    }

    final public function createItem(string $projectionName, string $projectionId, array $data) : void
    {
        $this->store($projectionName, $projectionId, $data);
    }

    final public function updateItem(string $projectionName, int $projectionId, array $data) : void
    {
        $this->store($projectionName, $projectionId, $data);
    }

    private function store(string $projectionName, string $projectionId, array $data) : void
    {
        $projectionStorageClient = $this->projectionStorageClient;
        $projectionSchemaClient = $this->projectionSchemaClient;

        $storeProjectionCommand = Handlers\StoreProjectionCommand::new($projectionName, $projectionId, $data);
        $storeProjectionHandler = Handlers\StoreProjectionHandler::new($projectionStorageClient,
            $projectionSchemaClient);
        $storeProjectionHandler->handle($storeProjectionCommand);
    }

    /** @return Domain\ProjectedRow[] */
    final public function getItemList(string $projectionName, array $filter) : array
    {
        $projectionStorageClient = $this->projectionStorageClient;
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $queriedRows = $projectionStorageClient->query($projectionName, $projectionSchema, $filter);
        if (count($queriedRows) > 0) {
            return $queriedRows;
        }

        /*
        $projectionStream = Domain\ProjectionStream::new($this->projectionStorageClient, $projectionName, $projectionSchema);
        $projectedRows = $projectionStream->getProjectedRows();
        if (count($projectedRows) > 0) {
            $items = [];
            foreach ($projectedRows as $projectedRow) {
                $items[] = $projectedRow->toArray();
            }
            return $items;
        }
        */
        return [];
    }

    final public function getItem(string $projectionName, string $projectionId) : array
    {
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $projectionStream = Domain\ProjectionStream::new($this->projectionStorageClient, $projectionName,
            $projectionSchema);
        return $projectionStream->getProjectedRowForProjectionId($projectionId)->toArray();
    }

    final public function getProjectionSchema(string $projectionName) : array
    {
        return $this->projectionSchemaClient->getProjectionSchema($projectionName);
    }

    private function assertFileExists(string $filePath) : void
    {
        if (file_exists($filePath) === false) {
            throw new Exception('File not exists ' . $filePath);
        }
    }

}