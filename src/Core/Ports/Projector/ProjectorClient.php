<?php
declare(strict_types=1);

namespace FluxEco\Projection\Core\Ports\Projector;

interface ProjectorClient {
    public function reprojectGlobalStreamStates(array $aggregateRootNames): void;
}