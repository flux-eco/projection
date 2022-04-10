<?php

namespace fluxProjection;

use FluxEco\{Projection, Projection\Adapters};

/** @return Adapters\AggregateRoot\AggregateRootMappingAdapter[] */
function getAggregateRootMappingsForProjectionData(string $projectionName, array $keyValueData): array
{
    return Projection\Api::newFromEnv()->getAggregateRootMappingsForProjectionData($projectionName, $keyValueData);
}