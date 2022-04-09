<?php


declare(strict_types=1);

namespace FluxEco\Projection\Core\Application\Handlers;

use FluxEco\Projection\Core\{Domain, Ports};

class StoreProjectionHandler implements Handler
{
    private Ports\Outbounds $outbounds;

    private function __construct(
        Ports\Outbounds $outbounds
    )
    {
        $this->outbounds = $outbounds;
    }

    public static function new(
        Ports\Outbounds $outbounds
    ): self
    {
        return new self($outbounds);
    }

    final public function handle(StoreProjectionCommand|Command $command)
    {
        $projectionName = $command->getProjectionName();
        $projectionId = $command->getProjectionId();
        $data = $command->getData();

        $projectionSchema = $this->outbounds->getProjectionSchema($projectionName);

        $projectionStream = Domain\ProjectionStream::new($this->outbounds, $projectionName, $projectionSchema);
        $projectionStream->projectData($projectionId, $data);
    }
}