<?php

namespace FluxEco\Projection\Core\Application\Handlers;

class DeleteProjectionStorageCommand implements Command
{
    private string $tableName;
    private array $schema;

    private function __construct(string $tableName, array $schema)
    {
        $this->tableName = $tableName;
        $this->schema  = $schema;
    }


    public static function new(string $tableName, array $schema): self
    {
        return new self($tableName, $schema);
    }

    final public function getTableName(): string
    {
        return $this->tableName;
    }

    final public function getSchema(): array
    {
        return $this->schema;
    }


    final public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}