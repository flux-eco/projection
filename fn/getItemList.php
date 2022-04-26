<?php

namespace fluxProjection;

use FluxEco\Projection;

function getItemList(
    string $projectionName,
    ?string $parentId = null,
    ?int $offset = null,
    ?int $limit = null,
    ?array $orderBy = null,
    ?string $search = null,
    ?array $filter = []
) : array {
    return Projection\Api::newFromEnv()->getItemList($projectionName, $parentId, $offset, $limit,
        Projection\OrderByRequest::fromArray($orderBy), $search);
}