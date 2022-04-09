<?php

namespace fluxProjection;

use FluxEco\Projection;

function receiveAggregateRootStatePublished(
    string $aggregateRootId,
    string $aggregateRootName,
    string $messageName,
    string $jsonAggregateRootSchema,
    string $payload
) {
    Projection\Api::newFromEnv()->receiveAggregateRootStatePublished(
        $aggregateRootId,
        $aggregateRootName,
        $messageName,
        $jsonAggregateRootSchema,
        $payload
    );
}