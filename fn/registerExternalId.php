<?php

namespace fluxProjection;

use FluxEco\Projection;
use Exception;

/**
 * @throws Exception
 */
function registerExternalId(string $projectionName, string $aggregateName, string $externalId): void
{
    Projection\Api::newFromEnv()->registerExternalId($projectionName, $aggregateName, $externalId);
}