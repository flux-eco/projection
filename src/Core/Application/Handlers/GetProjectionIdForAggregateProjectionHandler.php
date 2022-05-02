<?php

namespace FluxEco\Projection\Core\Application\Handlers;
use FluxEco\Projection\Core\Ports;

class GetProjectionIdForAggregateProjectionHandler implements Handler
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
        return new self(
            $outbounds
        );
    }

    public function handle(GetProjectionIdForAggregateProjectionCommand|Command $command): ?string {

        $mappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $schema = $this->outbounds->getProjectionSchema($mappingProjectionName);

        $projectionName = $command->getProjectionName();
        $aggregateId =  $command->getAggregateId();

        $filter = [
            'projectionName' => $projectionName,
            'aggregateId' => $aggregateId
        ];

        $result = $this->outbounds->queryProjectionStorage($mappingProjectionName, $schema, $filter);

        if (count($result) > 1) {
            throw new \RuntimeException('Inconsistent Database: There are more than one projections ' . $projectionName . ' for the same aggregate: ' . $aggregateId);
        }

        if (count($result) === 1) {
            return $result[0]['projectionId'];
        }

        return null;
    }
}