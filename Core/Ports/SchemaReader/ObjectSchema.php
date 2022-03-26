<?php

namespace Flux\Eco\AggregateRoot\Core\Ports\SchemaReader;

interface ObjectSchema
{
    public function getProperties(): SchemaProperties;
}