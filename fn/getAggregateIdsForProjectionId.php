<?php

namespace fluxProjection;

use FluxEco\Projection;

function getAggregateIdsForProjectionId(string $projectionName, string $projectionId): array {
    return Projection\Api::newFromEnv()->getAggregateIdsForProjectionId($projectionName, $projectionId);
}