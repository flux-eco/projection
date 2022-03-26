<?php

namespace FluxEco\Projection\Core\Ports\Configs;
use FluxEco\Projection\Core\Ports;

interface ProjectionOutbounds
{
    public function getProjectionStorageClient(): Ports\Storage\ProjectionStorageClient;
    public function getProjectionSchemaClient(): Ports\SchemaRegistry\ProjectionSchemaClient;
    public function getJsonSchemaAssertersClient(): Ports\Assert\AssertJsonSchemaClient;
    public function getValueObjectProvider(): Ports\ValueObjectProvider\ValueObjectProviderClient;
}