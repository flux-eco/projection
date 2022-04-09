<?php

namespace fluxProjection;

use FluxEco\Projection;

function getItem(string $projectionName, string $projectionId): array
{
    return Projection\Api::newFromEnv()->getItem($projectionName, $projectionId);
}