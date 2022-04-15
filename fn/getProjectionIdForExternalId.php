<?php

namespace fluxProjection;

use FluxEco\Projection;

function getProjectionIdForExternalId(string $projectionName, string $aggregateName, string $externalId): ?string
{
    return Projection\Api::newFromEnv()->getProjectionIdForExternalId($projectionName, $aggregateName, $externalId);
}