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
    private Outbounds $outbounds;

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

            $createProjectionStorage = Handlers\CreateProjectionStorageCommand::new($projectionName, $projectionSchema);
            $createProjectionStorageHandler = Handlers\CreateProjectionStorageHandler::new($this->outbounds);
            $createProjectionStorageHandler->handle($createProjectionStorage);
        }
    }

    final public function reinitalizeProjectionStorages() : void
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
        if($projectionSchemas === null) {
            return;
        }

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

    final public function getAggregateRootMappingsForProjectionData(string $projectionName, array $keyValueData) : array
    {
        $projectionSchema = $this->getProjectionSchema($projectionName);
        $mappings = [];

        if (count($keyValueData) > 0) {
            foreach ($keyValueData as $key => $value) {
                //todo assert key exists as property
                $propertyPath = $projectionSchema['properties'][$key]['index'];
                $propertyPathParts = explode('.', $propertyPath);

                if ($propertyPathParts[1] === 'rootObject') {
                    $aggregateRootPropertyKey = $propertyPathParts[2];
                    $aggregateName = $propertyPathParts[0];
                    $mappings[$aggregateName][$aggregateRootPropertyKey] = $value;
                }
            }
        }
        return $mappings;
    }

    final public function getProjectionIdForAggregateId(string $projectionName, string $aggregateId) : ?string
    {
        $aggregateRootMappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $aggregateRootMappingProjectionSchema = $this->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = ['aggregateId' => $aggregateId, 'projectionName' => $projectionName];
        $result = $this->outbounds->queryProjectionStorage($aggregateRootMappingProjectionName,
            $aggregateRootMappingProjectionSchema, $filter);
        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for external Id: ' . $aggregateId);
        }

        if (count($result) === 1) {
            return $result[0]['projectionId'];
        }
        return null;
    }

    /**
     * @throws Exception
     */
    final public function getProjectionIdForExternalId(
        string $projectionName,
        string $aggregateName,
        string $externalId,
        string $externalSource,
    ) : ?string {

        $aggregateId = $this->getAggregateIdForExternalId($aggregateName, $externalId, $externalSource);


        $command = Handlers\GetMappingAggregateRootIdProjectionIdCommand::new($aggregateName, $aggregateId, $projectionName);
        $aggregateRootMapping = Handlers\GetMappingAggregateRootIdProjectionIdHandler::new($this->outbounds)->handle($command);

        if ($aggregateRootMapping === null) {
            return null;
        }

        return $aggregateRootMapping->getProjectionId();
    }

    final public function getAggregateIdForProjectionId($projectionName, $projectionId, $aggregateName) : ?string
    {
        $aggregateRootMappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $aggregateRootMappingProjectionSchema = $this->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = [
            'projectionName' => $projectionName,
            'projectionId' => $projectionId,
            'aggregateName' => $aggregateName
        ];

        $result = $this->outbounds->queryProjectionStorage($aggregateRootMappingProjectionName,
            $aggregateRootMappingProjectionSchema, $filter);
        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for aggregateName: ' . $aggregateName . ', projectionId ' . $projectionId);
        }

        if (count($result) === 1) {
            return $result[0]['aggregateId'];
        }
        return null;
    }

    /**
     * @throws Exception
     */
    final public function getAggregateIdForExternalId(
        string $aggregateName,
        string $externalId,
        string $externalSource
    ) : ?string {
        $command = Handlers\GetMappingAggregateRootIdExternalIdCommand::new($aggregateName, $externalId, $externalSource);
        $aggregateRootMapping = Handlers\GetMappingAggregateRootIdExternalIdHandler::new($this->outbounds)->handle($command);

        if ($aggregateRootMapping === null) {
            return null;
        }

        return $aggregateRootMapping->getAggregateId();
    }

    final public function getAggregateIdsForProjectionId($projectionName, $projectionId) : array
    {
        $aggregateRootMappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $aggregateRootMappingProjectionSchema = $this->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = [
            'projectionName' => $projectionName,
            'projectionId' => $projectionId,
        ];

        $aggregateIds = [];
        $results = $this->outbounds->queryProjectionStorage(
            $aggregateRootMappingProjectionName,
            $aggregateRootMappingProjectionSchema,
            $filter
        );
        if (count($results) > 0) {
            foreach ($results as $result) {
                $aggregateIds[$result['aggregateName']] = $result['aggregateId'];
            }
        }

        return $aggregateIds;
    }

    /**
     * @throws Exception
     */
    final public function registerAggregateId(string $aggregateName, string $aggregateId, string $projectionName) : void
    {

        $command = Handlers\GetMappingAggregateRootIdProjectionIdCommand::new($aggregateName, $aggregateId, $projectionName);
        $aggregateRootMapping = Handlers\GetMappingAggregateRootIdProjectionIdHandler::new($this->outbounds)->handle($command);

        if ($aggregateRootMapping === null) {
            $projectionId = $this->outbounds->getNewUuid();

            $aggregateRootMapping = Domain\Models\AggregateRootIdProjectionIdMapping::new(
                $projectionName,
                $projectionId,
                $aggregateName,
                $aggregateId
            );
            $this->outbounds->storeAggregateRootIdProjectionIdMapping($aggregateRootMapping);
        }
    }

    /**
     * @throws Exception
     */
    final public function registerExternalId(string $aggregateName, string $externalId, string $externalSource) : void
    {

        $command = Handlers\GetMappingAggregateRootIdExternalIdCommand::new($aggregateName, $externalId, $externalSource);
        $aggregateRootMapping = Handlers\GetMappingAggregateRootIdExternalIdHandler::new($this->outbounds)->handle($command);

        if ($aggregateRootMapping === null) {
            $aggregateId = $this->outbounds->getNewUuid();

            $aggregateRootMapping = Domain\Models\AggregateRootIdExternalIdMapping::new(
                $aggregateName,
                $aggregateId,
                $externalId,
                $externalSource
            );
            $this->outbounds->storeAggregateRootIdExternalIdMapping($aggregateRootMapping);
        }
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
    final public function getItemList(
        string $projectionName,
        ?string $parentId = null,
        ?int $offset = null,
        ?int $limit = null,
        ?Domain\Models\OrderBy $orderBy = null,
        ?string $search = null,
        ?array $filter = null
    ) : array {
        $projectionSchema = $this->getProjectionSchema($projectionName);

        if(is_null($parentId) === false) {
            $filter['parentId'] = $parentId;
        }

        $orderByString = null;
        if(is_null($orderBy) === false) {
            $orderByString = $orderBy->toString();
        }

        $queriedRows = $this->outbounds->queryProjectionStorage($projectionName, $projectionSchema, $filter, $offset,
            $limit, $orderByString, $search);
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