<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class EvaluateRulesCommand implements Command
{
    private string $projectionName;
    private string $projectionId;
    private array $schema;
    private array $data;

    private function __construct(string $projectionName, string $projectionId, array $schema, array $data)
    {
        $this->projectionName = $projectionName;
        $this->projectionId  = $projectionId;
        $this->schema = $schema;
        $this->data  = $data;
    }


    public static function new(string $projectionName, string $projectionId, array $schema, array $data): self
    {
        return new self($projectionName, $projectionId, $schema, $data);
    }

    public function getProjectionName() : string
    {
        return $this->projectionName;
    }

    public function getProjectionId() : string
    {
        return $this->projectionId;
    }

    public function getSchema() : array
    {
        return $this->schema;
    }

    public function getData() : array
    {
        return $this->data;
    }

    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}