<?php

namespace FluxEco\Projection\Core\Ports\Assert;

interface AssertJsonSchemaClient
{
    public function assert(\JsonSerializable $value, array $jsonSchema): void;
}