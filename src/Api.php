<?php

namespace FluxEco\Projection;
use FluxEco\MessageServer;
use Exception;

class Api
{
    const EVENT_NAME_AGGREGATE_ROOT_DELETED = 'aggregateRootDeleted';

    private Adapters\Outbounds $outbounds;
    private Core\Ports\ProjectionService $projectionService;

    private function __construct(Adapters\Outbounds $outbounds)
    {
        $this->outbounds = $outbounds;
        $this->projectionService = Core\Ports\ProjectionService::new($outbounds);
    }

    public static function newFromEnv() : self
    {
        $env = Env::new();
        $outbounds = Adapters\Outbounds::new(
            $env->getProjectionAppSchemaDirectory(),
            $env->getProjectionEcoSchemaDirectory(),
            $env->getProjectionStorageConfigEnvPrefix()
        );
        return new self($outbounds);
    }

    final public function initialize() : void
    {
        $this->projectionService->initalizeProjectionStorages();
    }

    final public function reinitialize() : void
    {
        $this->projectionService->reinitalizeProjectionStorages();
    }

    final public function getAggregateRootMappingsForProjectionData(string $projectionName, array $keyValueData) : array
    {
        return $this->projectionService->getAggregateRootMappingsForProjectionData($projectionName, $keyValueData);
    }

    final public function receiveAggregateRootStatePublished(
        string $aggregateRootId,
        string $aggregateRootName,
        string $messageName,
        string $payload
    ) : void {
        $items = Adapters\AggregateRoot\ProjectedValuesAdapter::fromJson($payload)->toDomain();

        switch ($messageName) {
            case self::EVENT_NAME_AGGREGATE_ROOT_DELETED:
                $this->projectionService->deleteProjectedRows($aggregateRootId);
                break;
            default:
                $this->projectionService->onReceiveAggregateRootChangedEvent($aggregateRootId, $aggregateRootName,
                    $items);
                break;
        }
    }

    final public function getAggregateIdForProjectionId(
        string $projectionName,
        string $projectionId,
        string $aggregateName
    ) : string {
        return $this->projectionService->getAggregateIdForProjectionId($projectionName, $projectionId, $aggregateName);
    }

    final public function getAggregateIdsForProjectionId(string $projectionName, string $projectionId) : array
    {
        return $this->projectionService->getAggregateIdsForProjectionId($projectionName, $projectionId);
    }

    final public function getItemList(
        string $projectionName,
        ?string $parentId,
        ?int $offset,
        ?int $limit,
        ?OrderByRequest $orderByRequest = null,
        ?string $search = null,
        ?array $filter = null,
    ) : array {
        $orderBy = null;
        $schema = $this->outbounds->getProjectionSchema($projectionName);

        //todo
        if (key_exists('select', $schema)) {
            $projectionName = $schema['select']['table'];
            $schema = $this->outbounds->getProjectionSchema($schema['select']['table']);
        }

        if (is_null($orderByRequest) === false) {
            $orderBy = $orderByRequest->toDomain($schema);
        }

        return $this->projectionService->getItemList($projectionName, $parentId, $offset, $limit, $orderBy, $search, $filter);
    }

    final public function getItem(string $projectionName, string $projectionId) : array
    {
        return $this->projectionService->getItem($projectionName, $projectionId);
    }

    /** @return  Core\Domain\Models\[] */
    /*final public function getAggregateRootMappingsForAggregateId(string $projectionName, string $aggregateRootId): array  {
        $aggregateRootMappings = $this->projectionService->getAggregateRootMappingsForAggregateId($projectionName, $aggregateRootId);

        $mappings = [];
        foreach($aggregateRootMappings as $aggregateRootMapping) {
            $mappings[] = Adapters\AggregateRoot\RootObjectMapping::fromDomain($aggregateRootMapping);
        }
        return $mappings;
    }*/

    final public function getProjectionIdForAggregateId(string $projectionName, string $aggregateId) : ?string
    {
        return $this->projectionService->getProjectionIdForAggregateId($projectionName, $aggregateId);
    }

    /**
     * @throws Exception
     */
    final public function getProjectionIdForExternalId(
        string $projectionName,
        string $aggregateName,
        string $externalId,
        string $externalSource
    ) : ?string {
        return $this->projectionService->getProjectionIdForExternalId($projectionName, $aggregateName, $externalId, $externalSource);
    }

    final public function getAggregateIdForExternalId(
        string $aggregateName,
        string $externalId,
        string $externalSource
    ) : ?string {
        return $this->projectionService->getAggregateIdForExternalId($aggregateName, $externalId, $externalSource);
    }

    /**
     * @throws Exception
     */
    final public function registerExternalId(
        string $aggregateName,
        string $externalId,
        string $externalSource
    ) : void {
        $this->projectionService->registerExternalId($aggregateName, $externalId, $externalSource);
    }
}