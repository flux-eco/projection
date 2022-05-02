<?php

namespace FluxEco\Projection\Core\Application\Handlers;
use FluxEco\Projection\Core\{Ports, Domain};
use Exception;

class GetMappingAggregateRootIdExternalIdHandler implements Handler
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

    public function handle(GetMappingAggregateRootIdExternalIdCommand|Command $command): ?Domain\Models\AggregateRootIdExternalIdMapping {
        $mappingProjectionName = $this->outbounds->getProjectionNameMappingAggregateRootIdExternalId();
        $mappingProjectionSchema = $this->outbounds->getProjectionSchema($mappingProjectionName);
        $filter = [
            'aggregateName' => $command->getAggregateName(),
            'externalId' => $command->getExternalId(),
            'externalSource' => $command->getExternalSource()
        ];
        $result = $this->outbounds->queryProjectionStorage($mappingProjectionName, $mappingProjectionSchema, $filter);


        if (count($result) > 1) {
            throw new Exception('More than one mapping result found for externalId: ' . $command->getExternalId() .' and projection '.$command->getProjectionName().' aggregateName '.$command->getAggregateName());
        }

        if (count($result) === 1) {
            return Domain\Models\AggregateRootIdExternalIdMapping::fromArray($result[0]);
        }

        return null;
    }
}