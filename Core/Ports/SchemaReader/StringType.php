<?php

namespace Flux\Eco\AggregateRoot\Core\Ports\SchemaReader;

class StringType implements ValueType {

    public function __toString(): string
    {
        return 'string';
    }
}