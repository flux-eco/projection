<?php

namespace FluxEco\Projection\Core\Application\Handlers;
use FluxEco\Projection\Core\{Ports, Domain};
use Exception;

class GetAggregateRootMappingForExternalIdHandler implements Handler
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

    public function handle(GetAggregateRootMappingForExternalIdCommand|Command $command): ?Domain\Models\AggregateRootMapping {

        //todo get from stream because of to much queries

        $aggregateRootMappingProjectionName = $this->outbounds->getAggregateRootMappingProjectionName();
        $aggregateRootMappingProjectionSchema = $this->outbounds->getProjectionSchema($aggregateRootMappingProjectionName);
        $filter = [
            'projectionName' => $command->getProjectionName(),
            'aggregateName' => $command->getAggregateName(),
            'externalId' => $command->getExternalId()
        ];
        $result = $this->outbounds->queryProjectionStorage($aggregateRootMappingProjectionName, $aggregateRootMappingProjectionSchema, $filter);


        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for externalId: ' . $command->getExternalId() .' and projection '.$command->getProjectionName().' aggregateName '.$command->getAggregateName());
        }

        if (count($result) === 1) {
            return Domain\Models\AggregateRootMapping::fromArray($result[0]);
        }

        return null;
    }
}