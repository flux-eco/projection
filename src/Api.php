<?php

namespace FluxEco\Projection;

class Api
{
    const EVENT_NAME_AGGREGATE_ROOT_DELETED = 'aggregateRootDeleted';

    private Core\Ports\ProjectionService $projectionService;

    private function __construct(Adapters\Outbounds $outbounds)
    {
        $this->projectionService = Core\Ports\ProjectionService::new($outbounds);
    }

    public static function newFromEnv(): self
    {
        $env = Env::new();
        $outbounds = Adapters\Outbounds::new(
            $env->getProjectionAppSchemaDirectory(),
            $env->getProjectionEcoSchemaDirectory(),
            $env->getProjectionStorageConfigEnvPrefix()
        );
        return new self($outbounds);
    }

    final public function initialize(): void
    {
        $this->projectionService->initalizeProjectionStorages();
    }

    final public function getAggregateRootMappingsForProjectionData(string $projectionName, array $keyValueData): array {
        return $this->projectionService->getAggregateRootMappingsForProjectionData($projectionName, $keyValueData);
    }

    final public function receiveAggregateRootStatePublished(
        string $aggregateRootId,
        string $aggregateRootName,
        string $messageName,
        string $jsonAggregateRootSchema,
        string $payload
    ): void
    {
        $aggregateRootSchema = json_decode($jsonAggregateRootSchema, true);
        $items = Adapters\AggregateRoot\ProjectedValuesAdapter::fromJson($payload)->toDomain();

        switch($messageName) {
            case self::EVENT_NAME_AGGREGATE_ROOT_DELETED:
                $this->projectionService->deleteProjectedRows($aggregateRootId);
                break;
            default:
                $this->projectionService->onReceiveAggregateRootChangedEvent($aggregateRootId, $aggregateRootName, $items);
                break;
        }
    }


    final public function getItemList(string $projectionName, array $filter): array
    {
        return $this->projectionService->getItemList($projectionName, $filter);
    }

    final public function getItem(string $projectionName, string $projectionId): array
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


    final public function getProjectionIdForAggregateId(string $projectionName, string $aggregateId): ?string  {
        return $this->projectionService->getProjectionIdForAggregateId($projectionName, $aggregateId);
    }

    final public function getProjectionIdForExternalId(string $projectionName, string $externalId): ?string  {
        return $this->projectionService->getProjectionIdForExternalId($projectionName, $externalId);
    }



}