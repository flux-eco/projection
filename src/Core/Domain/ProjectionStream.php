<?php

namespace FluxEco\Projection\Core\Domain;

use FluxEco\Projection\Adapters\Storage\ProjectionStorageClient;

class ProjectionStream
{
    protected static array $instances = [];

    private int $total = 0;
    private int $totalLoaded = 0;
    private int $sequenceOffSet = 0;
    private int $limit = 0;
    private string $projectionName;
    private array $schema;


    /** @var ProjectedRow[] */
    private array $projectedRows = [];
    /** @var ProjectedRow[] */
    private array $subjectIdMapping = [];

    private ProjectionStorageClient $projectionStorageClient;
    /** @var ProjectedRow[] */
    private array $recordedRows = [];

    private function __construct(
        ProjectionStorageClient $projectionStorageClient,
        string                  $projectionName,
        array                   $schema,
        int                     $sequenceOffSet = 0,
        int                     $limit = 0
    )
    {
        $this->projectionStorageClient = $projectionStorageClient;
        $this->projectionName = $projectionName;
        $this->schema = $schema;
        $this->sequenceOffSet = $sequenceOffSet;
        $this->limit = $limit;

        $this->loadProjectedRows($sequenceOffSet, $limit);
        $this->loadNumberOfTotalRows();
    }

    public static function new(
        ProjectionStorageClient $projectionStorageClient,
        string                  $projectionName,
        array                   $schema,
        int                     $sequenceOffSet = 0,
        int                     $limit = 0
    ): self
    {
        if (empty(static::$instances[$projectionName]) === true) {
            static::$instances[$projectionName] = new self(
                $projectionStorageClient,
                $projectionName,
                $schema,
                $sequenceOffSet,
                $limit
            );
        }
        return static::$instances[$projectionName];
    }

    private function loadNumberOfTotalRows(): void
    {
        $totalRows = $this->projectionStorageClient->countTotalRows();
        $this->total = $totalRows;
    }

    final public function loadProjectedRows(int $sequenceOffSet = 0, int $limit = 0): void
    {
        $queriedRows = $this->projectionStorageClient->queryProjectionStorage($this->projectionName, $this->schema, $sequenceOffSet, $limit);
        foreach ($queriedRows as $projectedRow) {
            $this->apply($projectedRow);
        }
        $this->totalLoaded = count($this->projectedRows);
    }

    final public function projectData(string $projectionId, array $data): void {
        foreach ($data as $key => $value) {
            $this->applyAndRecord($projectionId, $key, $value);
        }
        $this->storeRecordedRows();
    }

    private function applyAndRecord(string $projectionId, string $propertyKey, mixed $value): void
    {
        $projectedRow = $this->getProjectedRowForProjectionId($projectionId);
        $projectedRow->evaluate($propertyKey, $value);

        $this->apply($projectedRow);
        $this->record($projectedRow);
    }


    private function record(ProjectedRow $row): void
    {
        $this->recordedRows[$row->getProjectionId()] = $row;
    }

    final public function hasRecordedRows(): bool
    {
        return (count($this->recordedRows) > 0);
    }

    final public function flushRecordedRows(): void
    {
        $this->recordedRows = [];
    }

    private function apply(ProjectedRow $row): self
    {
        $this->projectedRows[$row->getProjectionId()] = $row;
        return $this;
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->projectedRows);
    }

    final public function projectedRowExists(string $projectionId): bool
    {
        return array_key_exists($projectionId, $this->projectedRows);
    }

    final public function getProjectedRowForProjectionId(string $projectionId): ProjectedRow
    {
        if ($this->projectedRowExists($projectionId) === true) {
            return $this->projectedRows[$projectionId];
        }
        return ProjectedRow::new($projectionId, $this->schema);
    }


    final public function offsetUnset(string $projectionId): void
    {
        unset($this->projectedRows[$projectionId]);
    }

    final public function count(): int
    {
        return count($this->projectedRows);
    }

    final public function toArray(): array
    {
        return $this->projectedRows;
    }

    final public function hasProjectedRows(): bool
    {
        return (count($this->projectedRows) > 0);
    }

    final public function getProjectedRows(): array
    {
        return $this->projectedRows;
    }

    final public function getTotal(): int
    {
        return $this->total;
    }

    final public function getTotalLoaded(): int
    {
        return $this->totalLoaded;
    }


    final public function getSequenceOffSet(): int
    {
        return $this->sequenceOffSet;
    }

    final public function getLimit(): int
    {
        return $this->limit;
    }

    final public function getSchema(): array
    {
        return $this->schema;
    }

    final public function getSubjectIdMapping(): array
    {
        return $this->subjectIdMapping;
    }

    final public function getRecordedRows(): array
    {
        return $this->recordedRows;
    }

    private function storeRecordedRows(): void
    {
        if ($this->hasRecordedRows() === true) {
            $this->projectionStorageClient->storeRecordedRows(
                $this->projectionName,
                $this->schema,
                $this->recordedRows
            );
        }
    }
}