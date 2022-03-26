<?php

namespace FluxEco\Projection\Adapters\Configs;

use FluxEco\Projection\{Adapters, Core\Ports};

class ProjectionOutbounds implements Ports\Configs\ProjectionOutbounds
{
    private string $databaseName;

    private function __construct(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    public static function new(): self
    {
        $databaseName = getenv(ProjectionEnv::PARAM_DATABASE);
        return new self($databaseName);
    }

    final public function getProjectionStorageClient(): Ports\Storage\ProjectionStorageClient
    {
        //array $jsonSchema, string $subjectId, string $subjectName
        return Adapters\Storage\ProjectionStorageClient::new($this->databaseName);
    }

    final public function getProjectionSchemaClient(): Ports\SchemaRegistry\ProjectionSchemaClient
    {
        $uiSchemaDirectory = getenv(ProjectionEnv::PARAM_APP_PROJECTION_SCHEMA_DIRECTORY);
        $generalSchemaDirectory = getenv(ProjectionEnv::PARAM_ECO_PROJECTION_SCHEMA_DIRECTORY);
        return  Adapters\SchemaRegistry\ProjectionSchemaClient::new([$uiSchemaDirectory, $generalSchemaDirectory]);
    }

    final public function getJsonSchemaAssertersClient(): Ports\Assert\AssertJsonSchemaClient
    {
        return Adapters\Assert\AssertJsonSchemaClient::new();
    }

    final public function getValueObjectProvider(): Ports\ValueObjectProvider\ValueObjectProviderClient {
        return Adapters\ValueObjectProvider\ValueObjectProviderClient::new();
    }


}