<?php

namespace FluxEco\Projection\Core\Application\Handlers;
use FluxEco\Projection\Core\{Ports, Domain};
use Exception;

class GetMappingAggregateRootIdProjectionIdHandler implements Handler
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

    public function handle(GetMappingAggregateRootIdProjectionIdCommand|Command $command): ?Domain\Models\AggregateRootIdProjectionIdMapping {
        $mappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdProjectionId();
        $mappingProjectionSchema = $this->outbounds->getProjectionSchema($mappingProjectionName);
        $filter = [
            'aggregateName' => $command->getAggregateName(),
            'aggregateId' => $command->getAggregateRootId(),
            'projectionName' => $command->getProjectionName()
        ];
        $result = $this->outbounds->queryProjectionStorage($mappingProjectionName, $mappingProjectionSchema, $filter);


        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for aggregateId: ' . $command->getAggregateRootId() .' and projection '.$command->getProjectionName().' aggregateName '.$command->getAggregateName());
        }

        if (count($result) === 1) {
            return Domain\Models\AggregateRootIdProjectionIdMapping::fromArray($result[0]);
        }

        return null;
    }
}