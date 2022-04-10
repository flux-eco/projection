<?php

namespace fluxProjection;

use FluxEco\Projection;

function getAggregateIdForProjectionId(string $projectionName, string $projectionId, string $aggregateName): ?string {
    return Projection\Api::newFromEnv()->getAggregateIdForProjectionId($projectionName, $projectionId, $aggregateName);
}