<?php

namespace FluxEco\Projection\Core\Domain;

use FluxEco\Projection\Core\{Domain};

class ProjectedRow
{
    private array $schema;
    private Domain\Models\RowValues $evaluatedProperties;
    private Domain\Models\RowValues $unevaluatedProperties;

    private function __construct(
        string $projectionId,
        array  $schema
    )
    {
        $this->schema = $schema;

        $properties = $schema['properties'];
        $this->unevaluatedProperties = Domain\Models\RowValues::new();
        foreach ($properties as $key => $value) {
            $this->unevaluatedProperties->offsetSet($key, $value);
        }
        $this->evaluatedProperties = Domain\Models\RowValues::new();

        $this->evaluate( 'projectionId',$projectionId);
    }

    public static function new(
        string $projectionId,
        array  $schema,
    ): self
    {
        return new self(
            $projectionId,
            $schema
        );
    }

    public static function fromArray(
        array $rowValues,
        array $schema
    ): self
    {
        $obj = new self($rowValues['projectionId'], $schema);
        foreach ($rowValues as $key => $value) {
            $obj->evaluate($key, $value);
        }
        return $obj;
    }

    public function evaluate(string $propertyKey, mixed $propertyValue): void
    {
        if (array_key_exists($propertyKey, $this->schema['properties'])) {
            $this->evaluatedProperties->offsetSet($propertyKey, $propertyValue);
            $this->unevaluatedProperties->offsetUnset($propertyKey);
        }
    }

    final public function getProjectionId(): string
    {
        return  $this->evaluatedProperties->offsetGet('projectionId');
    }

    final public function completed(): bool
    {
        return ($this->unevaluatedProperties->count() === 0);
    }

    final public function getEvaluatedProperties(): Models\RowValues
    {
        return $this->evaluatedProperties;
    }

    final public function getUnevaluatedProperties(): Models\RowValues
    {
        return $this->unevaluatedProperties;
    }

    final public function getSchema(): array
    {
        return $this->schema;
    }

    public function toArray(): array
    {
        return $this->evaluatedProperties->toArray();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}