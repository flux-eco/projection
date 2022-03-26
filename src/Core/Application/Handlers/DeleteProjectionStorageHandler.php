<?php

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Ports};

class DeleteProjectionStorageHandler implements Handler
{
    private Ports\Storage\ProjectionStorageClient $projectionStorageClient;

    private function __construct(
        Ports\Storage\ProjectionStorageClient $projectionStorageClient)
    {
        $this->projectionStorageClient = $projectionStorageClient;
    }

    public static function new(
        Ports\Storage\ProjectionStorageClient $projectionStorageClient
    ): self
    {
        return new self(
            $projectionStorageClient
        );
    }


    final public function handle(Command|DeleteProjectionStorageCommand $command)
    {
        $tableName = $command->getTableName();
        $schema = $command->getSchema();
        $this->projectionStorageClient->deleteProjectionStorage($tableName, $schema);
    }

}