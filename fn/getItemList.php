<?php

namespace fluxProjection;

use FluxEco\Projection;

function getItemList(string $projectionName, array $filter): array
{
    return Projection\Api::newFromEnv()->getItemList($projectionName, $filter);
}