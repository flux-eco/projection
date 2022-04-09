<?php

namespace fluxProjection;

use FluxEco\Projection;

function getProjectionIdForAggregateId(string $projectionName, string $aggregateRootId): ?string {
    return Projection\Api::newFromEnv()->getProjectionIdForAggregateId($projectionName, $aggregateRootId);
}