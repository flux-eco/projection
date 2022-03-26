<?php

namespace FluxEco\Projection\Core\Ports\Storage;

interface ProjectedItems
{
    public function getProjectionName(): string;
    public function getNextSequence(): int;
    public function hasProjectedItems(): bool;
    public function getProjectedItems(): array;
}