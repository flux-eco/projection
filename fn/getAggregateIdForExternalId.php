<?php

namespace fluxProjection;

use FluxEco\Projection;

function getAggregateIdForExternalId(string $aggregateName, string $externalId, string $externalSource): ?string {
    return Projection\Api::newFromEnv()->getAggregateIdForExternalId($aggregateName, $externalId, $externalSource);
}