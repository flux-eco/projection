<?php

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Ports};

class CreateProjectionStorageHandler implements Handler
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


    final public function handle(Command|CreateProjectionStorageCommand $command)
    {
        $tableName = $command->getTableName();
        $schema = $command->getSchema();
        $this->projectionStorageClient->createProjectionStorage($tableName, $schema);
    }

}