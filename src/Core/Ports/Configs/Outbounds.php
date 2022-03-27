<?php

namespace FluxEco\Projection\Core\Ports\Configs;
use FluxEco\Projection\Core\Ports;

interface Outbounds
{
    public function getProjectionStorageClient(): Ports\Storage\ProjectionStorageClient;
    public function getProjectionSchemaClient(): Ports\SchemaRegistry\ProjectionSchemaClient;
    public function getJsonSchemaAssertersClient(): Ports\Assert\AssertJsonSchemaClient;
    public function getValueObjectProvider(): Ports\ValueObjectProvider\ValueObjectProviderClient;
    public function getProjectorClient(): Ports\Projector\ProjectorClient;
    public function getProjectionSchemaDirectories(): array;
}