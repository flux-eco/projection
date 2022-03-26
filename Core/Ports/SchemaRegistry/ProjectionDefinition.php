<?php

namespace FluxEco\Projection\Core\Ports\SchemaRegistry;
use FluxEco\Projection\Core\Domain;

interface ProjectionDefinition {
    public function getProjectionName(): string;
    public function getSchemaFilePath(): string;
}