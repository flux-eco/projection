<?php

namespace FluxEco\Projection\Adapters\Storage;
use FluxEco\Projection\Core\Domain\ProjectedRow;

class ProjectedRowsAdapter
{
    /** @var ProjectedRow[]  */
    private array $rows;

    private function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public static function fromQueryResult(array $schema, array $queryResult): self
    {
        $rows = [];
        foreach ($queryResult as $row) {
            $rows[$row['projectionId']] =  ProjectedRow::fromArray($row, $schema);
        }
        return new self($rows);
    }

    /**
     * @return ProjectedRow[]
     */
    final public function toProjectedRows(): array
    {
        return $this->rows;
    }
}