<?php

namespace Flux\Eco\AggregateRoot\Core\Ports\SchemaReader;

interface SchemaObject
{
    public function getType(): string;
}