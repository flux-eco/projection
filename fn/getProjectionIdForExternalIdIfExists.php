<?php

namespace fluxProjection;

use FluxEco\Projection;

function getProjectionIdForExternalIdIfExists(string $projectionName, string $externalId)
{
    Projection\Api::newFromEnv()->getProjectionIdForExternalId($projectionName, $externalId);
}