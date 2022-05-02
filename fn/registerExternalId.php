<?php

namespace fluxProjection;

use FluxEco\Projection;
use Exception;

/**
 * @throws Exception
 */
function registerExternalId(string $aggregateName, string $externalId, string $externalSource): void
{
    Projection\Api::newFromEnv()->registerExternalId($aggregateName, $externalId, $externalSource);
}