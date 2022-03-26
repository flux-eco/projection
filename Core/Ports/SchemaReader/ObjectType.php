<?php

namespace Flux\Eco\AggregateRoot\Core\Ports\SchemaReader;

class ObjectType implements ValueType {

    public function __toString(): string
    {
        return 'object';
    }
}