<?php

namespace FluxEco\Projection\Core\Ports\SchemaReader;

use FluxEco\Projection\Core\Domain;

interface SchemaFileReader
{
    public function readSchemaFile(string $schemaFilePath): Domain\Models\SchemaDocument;
}