<?php

namespace FluxEco\Projection\Adapters\Configs;

use FluxEco\Projection\{Adapters, Core\Ports};

class Outbounds implements Ports\Configs\Outbounds
{
    private string $databaseName;

    private function __construct(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    public static function new() : self
    {
        $databaseName = getenv(ProjectionEnv::PARAM_DATABASE);
        return new self($databaseName);
    }

    final public function getProjectionStorageClient() : Ports\Storage\ProjectionStorageClient
    {
        //array $jsonSchema, string $subjectId, string $subjectName
        return Adapters\Storage\ProjectionStorageClient::new($this->databaseName);
    }

    final public function getProjectionSchemaClient() : Ports\SchemaRegistry\ProjectionSchemaClient
    {
        return Adapters\SchemaRegistry\ProjectionSchemaClient::new([
            $this->getAppSchemaDirectory(),
            $this->getOwnSchemaDirectory()
        ]);
    }

    private function getAppSchemaDirectory() : string
    {
        return getenv(ProjectionEnv::APP_PROJECTION_SCHEMA_DIRECTORY);
    }

    private function getOwnSchemaDirectory() : string
    {
        return getenv(ProjectionEnv::FLUXECO_PROJECTION_DIRECTORY) . '/schemas';
    }

    final public function getJsonSchemaAssertersClient() : Ports\Assert\AssertJsonSchemaClient
    {
        return Adapters\Assert\AssertJsonSchemaClient::new();
    }

    final public function getValueObjectProvider() : Ports\ValueObjectProvider\ValueObjectProviderClient
    {
        return Adapters\ValueObjectProvider\ValueObjectProviderClient::new();
    }

    final public function getProjectionSchemaDirectories() : array
    {
        return [
            $this->getAppSchemaDirectory(),
            $this->getOwnSchemaDirectory()
        ];
    }

    final public function getProjectorClient(): Ports\Projector\ProjectorClient {
        return Adapters\Projector\ProjectorClient::new();
    }

}