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
    private   Outbounds $outbounds;

    private function __construct(
        Outbounds $outbounds,
    ) {
        $this->outbounds = $outbounds;
    }

    public static function new(Outbounds $projectionOutbounds) : self
    {
        return new self(
            $projectionOutbounds
        );
    }

    final public function initalizeProjectionStorages() : void
    {
        foreach ($this->getProjectionSchemas() as $projectionSchema) {
            $projectionName = $projectionSchema['title'];

            $deleteProjectionStorage = Handlers\DeleteProjectionStorageCommand::new($projectionName, $projectionSchema);
            $deleteProjectionStorageHandler = Handlers\DeleteProjectionStorageHandler::new($this->outbounds);
            $deleteProjectionStorageHandler->handle($deleteProjectionStorage);

            $createProjectionStorage = Handlers\CreateProjectionStorageCommand::new($projectionName, $projectionSchema);
            $createProjectionStorageHandler = Handlers\CreateProjectionStorageHandler::new($this->outbounds);
            $createProjectionStorageHandler->handle($createProjectionStorage);

            if (key_exists('aggregateRootNames', $projectionSchema)) {
                $this->outbounds->reprojectGlobalStreamStates($projectionSchema['aggregateRootNames']);
            }
        }
    }

    private function getProjectionSchemas() : array
    {
        $projectionSchemas = [];
        foreach ($this->outbounds->getProjectionSchemaDirectories() as $projectionSchemaDirectory) {
            $projectionFiles = scandir($projectionSchemaDirectory);
            foreach ($projectionFiles as $projectionFile) {
                if (pathinfo($projectionFile, PATHINFO_EXTENSION) === "yaml") {
                    $projectionSchemas[] = yaml_parse(file_get_contents($projectionSchemaDirectory . '/' . $projectionFile));
                }
            }
        }
        return $projectionSchemas;
    }

    final public function deleteProjectedRows(
        string $aggregateId
    ) {
        $aggregateRootMappings = $this->getAggregateRootMappingsForAggregateId($aggregateId);

        if ($aggregateRootMappings !== null) {
            foreach ($aggregateRootMappings as $mapping) {
                $schema = $this->outbounds->getProjectionSchema($mapping->getProjectionName());
                $this->outbounds->deleteProjectedRow(
                    $mapping->getProjectionName(),
                    $schema,
                    $mapping->getProjectionId()
                );
            }
        }
    }


    final public function onReceiveAggregateRootChangedEvent(
        string $aggregateId,
        string $aggregateName,
        Domain\Models\RowValues $values
    ) : void {
        $projectionSchemas = $this->outbounds->getProjectionSchemasForAggregate($aggregateName);

        $command = RefreshProjectionsCommand::new(
            $aggregateId,
            $aggregateName,
            $projectionSchemas,
            $values
        );
        RefreshProjectionsProcess::new(
            $this->outbounds
        )->handle($command);
    }

    /** @return Domain\Models\RootObjectMapping[] */
    final public function getAggregateRootMappingsForAggregateId(string $aggregateId) : ?array
    {
        $mappingProjectionName = 'AggregateRootMapping';
        $schema = $this->getProjectionSchema($mappingProjectionName);
        $filter = [
            'aggregateId' => $aggregateId
        ];

        $results = $this->outbounds->queryProjectionStorage($mappingProjectionName, $schema, $filter);
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

    /** @return ?Domain\Models\AggregateRootMapping[] */
    final public function getAggregateRootMappingsForProjectionId(string $projectionId) : ?array
    {
        $mappingProjectionName = $this->outbounds->getAggregateRootMappingProjectionName();
        $mappingProjectionSchema = $this->getProjectionSchema($mappingProjectionName);
        $filter = ['projectionId' => $projectionId];
        $result = $this->outbounds->queryProjectionStorage($mappingProjectionName, $mappingProjectionSchema, $filter);
        if (count($result) > 0) {
            $aggregateRootMappings = [];
            foreach ($result as $key => $value) {
                $aggregateRootMappings[] = Domain\Models\AggregateRootMapping::new(
                    $value['projectionName'],
                    $value['projectionId'],
                    $value['aggregateName'],
                    $value['aggregateId']
                );
            }
            return $aggregateRootMappings;
        }
        return null;
    }

    final public function  getProjectionIdForAggregateId(string $projectionName, string $aggregateId): ?string {
        $aggregateRootMappingProjectionName = $this->outbounds->getAggregateRootMappingProjectionName();
        $aggregateRootMappingProjectionSchema = $this->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = ['aggregateId' => $aggregateId, 'projectionName' => $projectionName];
        $result = $this->outbounds->queryProjectionStorage($aggregateRootMappingProjectionName, $aggregateRootMappingProjectionSchema, $filter);
        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for external Id: ' . $externalId);
        }

        if (count($result) === 1) {
            return $result[0]['projectionId'];
        }
        return null;
    }

    final public function getProjectionIdForExternalId(string $projectionName, string $externalId) : ?string
    {

        $aggregateRootMappingProjectionName = 'AggregateRootMapping';
        $aggregateRootMappingProjectionSchema = $this->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = [
            'projectionName' => $projectionName,
            'externalId' => $externalId
        ];

        $result = $this->outbounds->queryProjectionStorage($aggregateRootMappingProjectionName, $aggregateRootMappingProjectionSchema, $filter);
        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for external Id: ' . $externalId);
        }

        if (count($result) === 1) {
            return $result[0]['projectionId'];
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
                        $aggregateId = $this->outbounds->getNewUuid();

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
        $storeProjectionCommand = Handlers\StoreProjectionCommand::new($projectionName, $projectionId, $data);
        $storeProjectionHandler = Handlers\StoreProjectionHandler::new($this->outbounds);
        $storeProjectionHandler->handle($storeProjectionCommand);
    }

    /** @return Domain\ProjectedRow[] */
    final public function getItemList(string $projectionName, array $filter) : array
    {
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $queriedRows = $this->outbounds->queryProjectionStorage($projectionName, $projectionSchema, $filter);
        if (count($queriedRows) > 0) {
            return $queriedRows;
        }
        return [];
    }

    final public function getItem(string $projectionName, string $projectionId) : array
    {
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $projectionStream = Domain\ProjectionStream::new(
            $this->outbounds,
            $projectionName,
            $projectionSchema
        );
        return $projectionStream->getProjectedRowForProjectionId($projectionId)->toArray();
    }

    final public function getProjectionSchema(string $projectionName) : array
    {
        return $this->outbounds->getProjectionSchema($projectionName);
    }

    private function assertFileExists(string $filePath) : void
    {
        if (file_exists($filePath) === false) {
            throw new Exception('File not exists ' . $filePath);
        }
    }

}