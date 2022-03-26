<?php

namespace FluxEco\Projection\Core\Ports\SchemaInstanceProvider;

use FluxEco\AggregateRoot\Core\Domain;

interface SchemaInstanceProvider
{
    public function provideRootObject(mixed $value, array $schema): Domain\Models\RootObject;
}