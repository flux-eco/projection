<?php

namespace FluxEco\Projection\Core\Application\Processes;

use FluxEco\Projection\Core\{Application\Handlers, Domain, Ports};

//todo we should think about queryHandler WITH return values and command handler WITHOUT  return values

/**
 * @author martin@fluxlabs.ch
 */
class RefreshProjectionsProcess
{
    private Ports\Outbounds $outbounds;

    private function __construct(
        Ports\Outbounds $outbounds
    )
    {
        $this->outbounds = $outbounds;
    }

    public static function new(
        Ports\Outbounds $outbounds
    ): self
    {
        return new self($outbounds);
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


        foreach ($projectionSchemas as $projectionSchema) {
            $projectionName = $projectionSchema['title'];

            $externalId = null;
            if (empty($projectionSchema['externalIdName']) === false) {
                $externalId = $rowValues->offsetGet($projectionSchema['externalIdName']);
            }

            $getProjectionIdForAggregateProjectionCommand = Handlers\GetProjectionIdForAggregateProjectionCommand::new($projectionName, $aggregateId);
            $projectionId = Handlers\GetProjectionIdForAggregateProjectionHandler::new($this->outbounds)->handle($getProjectionIdForAggregateProjectionCommand);

            if ($projectionId === null) {
                $projectionId = $this->outbounds->getNewUuid();
                $storeProjectionAggregateMappingCommand = Handlers\StoreProjectionAggregateMappingCommand::new($projectionName, $projectionId, $aggregateName, $aggregateId, $externalId);
                $storeProjectionAggregateMappingHandler = Handlers\StoreProjectionAggregateMappingHandler::new($this->outbounds);
                $this->processCommand($storeProjectionAggregateMappingCommand, $storeProjectionAggregateMappingHandler);
            }

            $storeProjectionCommand = Handlers\StoreProjectionCommand::new($projectionName, $projectionId, $rowValues->toArray());
            $storeProjectionHandler = Handlers\StoreProjectionHandler::new($this->outbounds);
            $this->processCommand($storeProjectionCommand, $storeProjectionHandler);
        }
    }

    private function processCommand(Handlers\Command $command, Handlers\Handler $handler): void
    {
        $handler->handle($command);
    }
}