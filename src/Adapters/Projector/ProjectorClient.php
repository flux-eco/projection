<?php


namespace FluxEco\Projection\Adapters\Projector;
use FluxEco\Projection\{Core\Ports};
use FluxEco\GlobalStream\Adapters\Api\GlobalStreamApi;

class ProjectorClient implements Ports\Projector\ProjectorClient
{

    private function __construct() {}

    public static function new(): self
    {
        return new self();
    }

    public function reprojectGlobalStreamStates(array $aggregateRootNames): void
    {
        $globalStreamApi = GlobalStreamApi::new(
            $aggregateRootNames
        );
        $globalStreamApi->republishAllStates();
    }
}