<?php

namespace fluxProjection;

use FluxEco\Projection;

function getAggregateIdForExternalId(string $projectionName, string $aggregateName, string $externalId): ?string {
    return Projection\Api::newFromEnv()->getAggregateIdForExternalId($projectionName, $aggregateName, $externalId);
}