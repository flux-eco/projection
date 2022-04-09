<?php

namespace fluxProjection;

use FluxEco\{Projection, Projection\Adapters};

/** @return Adapters\AggregateRoot\AggregateRootMappingAdapter[] */
function getAggregateRootMappingsForProjectionId(string $projectionId): array
{
    return Projection\Api::newFromEnv()->getAggregateRootMappingsForProjectionId($projectionId);
}