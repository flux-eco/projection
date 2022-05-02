<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class GetMappingAggregateRootIdExternalIdCommand implements Command
{
    private string $aggregateName;
    private string $externalId;
    private string $externalSource;

    private function __construct(string $aggregateName, string $externalId, string $externalSource)
    {
        $this->aggregateName = $aggregateName;
        $this->externalId  = $externalId;
        $this->externalSource  = $externalSource;
    }


    public static function new(string $aggregateName, string $externalId, string $externalSource): self
    {
        return new self($aggregateName, $externalId, $externalSource);
    }

    public function getAggregateName() : string
    {
        return $this->aggregateName;
    }

    public function getExternalId() : string
    {
        return $this->externalId;
    }

    public function getExternalSource(): string
    {
        return $this->externalSource;
    }


    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}