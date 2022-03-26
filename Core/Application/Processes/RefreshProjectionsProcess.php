<?php

namespace FluxEco\Projection\Core\Application\Processes;

use FluxEco\Projection\Core\{Application\Handlers, Domain, Ports};

//todo we should think about queryHandler WITH return values and command handler WITHOUT  return values

/**
 * @author martin@fluxlabs.ch
 */
class RefreshProjectionsProcess
{
    private Ports\Storage\ProjectionStorageClient $projectionStorageClient;
    private Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient;
    private Ports\ValueObjectProvider\ValueObjectProviderClient $valueObjectProviderClient;

    private function __construct(
        Ports\Storage\ProjectionStorageClient               $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient         $projectionSchemaClient,
        Ports\ValueObjectProvider\ValueObjectProviderClient $valueObjectProviderClient
    )
    {
        $this->projectionStorageClient = $projectionStorageClient;
        $this->projectionSchemaClient = $projectionSchemaClient;
        $this->valueObjectProviderClient = $valueObjectProviderClient;
    }

    public static function new(
        Ports\Storage\ProjectionStorageClient               $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient         $projectionSchemaClient,
        Ports\ValueObjectProvider\ValueObjectProviderClient $valueObjectProviderClient
    ): self
    {
        return new self($projectionStorageClient, $projectionSchemaClient, $valueObjectProviderClient);
    }

    /**
     * @throws \JsonException
     */
    public function handle(RefreshProjectionsCommand $command): void
    {
        $aggregateId = $command->getAggregateId();
        $aggregateName = $command->getAggregateName();
        $projectionSchemas = $command->getProjectionSchemas();
        $rowValues = $command->getItems();


        $projectionStorageClient = $this->projectionStorageClient;
        $projectionSchemaClient = $this->projectionSchemaClient;

        foreach ($projectionSchemas as $projectionSchema) {
            $projectionName = $projectionSchema['name'];

            $externalId = null;
            if (empty($projectionSchema['externalIdName']) === false) {
                $externalId = $rowValues->offsetGet($projectionSchema['externalIdName']);
            }

            $getProjectionIdForAggregateProjectionCommand = Handlers\GetProjectionIdForAggregateProjectionCommand::new($projectionName, $aggregateId);
            $projectionId = Handlers\GetProjectionIdForAggregateProjectionHandler::new($projectionStorageClient, $projectionSchemaClient)->handle($getProjectionIdForAggregateProjectionCommand);

            if ($projectionId === null) {
                $projectionId = $this->valueObjectProviderClient->createUuid();
                $storeProjectionAggregateMappingCommand = Handlers\StoreProjectionAggregateMappingCommand::new($projectionName, $projectionId, $aggregateName, $aggregateId, $externalId);
                $storeProjectionAggregateMappingHandler = Handlers\StoreProjectionAggregateMappingHandler::new($projectionStorageClient, $projectionSchemaClient);
                $this->processCommand($storeProjectionAggregateMappingCommand, $storeProjectionAggregateMappingHandler);
            }

            $storeProjectionCommand = Handlers\StoreProjectionCommand::new($projectionName, $projectionId, $rowValues->toArray());
            $storeProjectionHandler = Handlers\StoreProjectionHandler::new($projectionStorageClient, $projectionSchemaClient);
            $this->processCommand($storeProjectionCommand, $storeProjectionHandler);
        }
    }

    private function processCommand(Handlers\Command $command, Handlers\Handler $handler): void
    {
        $handler->handle($command);
    }
}