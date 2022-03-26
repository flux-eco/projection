<?php

namespace Flux\Eco\AggregateRoot\Core\Ports\SchemaReader;

interface SchemaProperties extends \ArrayAccess, \IteratorAggregate
{
    public function offsetGet(mixed $offset): SchemaObject;
}