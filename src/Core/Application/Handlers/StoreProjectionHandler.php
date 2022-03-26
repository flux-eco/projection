<?php


declare(strict_types=1);

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Domain, Ports};

class StoreProjectionHandler implements Handler
{
    private Ports\Storage\ProjectionStorageClient $projectionStorageClient;
    private Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient;

    private function __construct(
        Ports\Storage\ProjectionStorageClient       $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient
    )
    {
        $this->projectionStorageClient = $projectionStorageClient;
        $this->projectionSchemaClient = $projectionSchemaClient;
    }

    public static function new(
        Ports\Storage\ProjectionStorageClient       $projectionStorageClient,
        Ports\SchemaRegistry\ProjectionSchemaClient $projectionSchemaClient
    ): self
    {
        return new self($projectionStorageClient, $projectionSchemaClient);
    }

    final public function handle(StoreProjectionCommand|Command $command)
    {
        $projectionName = $command->getProjectionName();
        $projectionId = $command->getProjectionId();
        $data = $command->getData();

        $projectionStorageClient = $this->projectionStorageClient;
        $projectionSchema = $this->projectionSchemaClient->getProjectionSchema($projectionName);

        $projectionStream = Domain\ProjectionStream::new($projectionStorageClient, $projectionName, $projectionSchema);
        $projectionStream->projectData($projectionId, $data);
    }
}