<?php


namespace FluxEco\Projection\Adapters\Api;

use FluxEco\GlobalStream\Adapters\Api;
use FluxEco\Projection\{Adapters, Core\Ports};

class ProjectionApi
{
    const EVENT_NAME_AGGREGATE_ROOT_DELETED = 'aggregateRootDeleted';

    private Ports\ProjectionService $projectionService;

    private function __construct(Ports\ProjectionService $projectionService)
    {
        $this->projectionService = $projectionService;
    }

    public static function new(): self
    {
        $projectionConfig = Adapters\Configs\ProjectionOutbounds::new();
        $projectionService = Ports\ProjectionService::new($projectionConfig);
        return new self($projectionService);
    }

    final public function initializeProjection(array $projectionSchema): void
    {
        $this->projectionService->initalizeProjectionStorage($projectionSchema);
    }

    final public function getAggregateRootMappingsForProjectionId(string $projectionId): ?array {
        return $this->projectionService->getAggregateRootMappingsForProjectionId($projectionId);
    }

    final public function receiveAggregateRootStatePublished(Api\StatePublished $statePublished): void
    {
        $subjectId = $statePublished->getSubjectId();
        $subjectName = $statePublished->getSubjectName();

        $messageName = $statePublished->getMessageName();
        $rootObjecSchema = json_decode($statePublished->getJsonRootObjectSchema(), true);
        $items = ProjectedValuesAdapter::fromJson($statePublished->getPayload(), $rootObjecSchema)->toDomain();

        switch($messageName) {
            case self::EVENT_NAME_AGGREGATE_ROOT_DELETED:
                $this->projectionService->deleteProjectedRows($subjectId, $subjectName);
                break;
            default:
                $this->projectionService->onReceiveAggregateRootChangedEvent($subjectId, $subjectName, $items);
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

    /** @return RootObjectMapping[] */
    final public function getAggregateRootMapping(string $projectionName, string $projectionId, array $data, ?string $externalId = null): array  {
        $aggregateRootMappings = $this->projectionService->getAggregateRootMappings($projectionName, $projectionId, $data, $externalId);

        $mappings = [];
        foreach($aggregateRootMappings as $aggregateRootMapping) {
            $mappings[] = RootObjectMapping::fromDomain($aggregateRootMapping);
        }
        return $mappings;
    }

    final public function getProjectionIdForExternalIdIfExists(string $projectionName, string $externalId): ?string  {
        return $this->projectionService->getProjectionIdForExternalIdIfExists($projectionName, $externalId);
    }



}