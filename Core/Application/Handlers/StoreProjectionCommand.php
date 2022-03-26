<?php


declare(strict_types=1);

namespace FluxEco\Projection\Core\Application\Handlers;

class StoreProjectionCommand implements Command
{
    private string $projectionName;
    private string $projectionId;
    private array $data;


    private function __construct(string $projectionName, string $projectionId, array $data)
    {
        $this->projectionName = $projectionName;
        $this->projectionId = $projectionId;
        $this->data = $data;
    }

    public static function new(
        string $projectionName, string $projectionId, array $data
    ): self
    {
        return new self($projectionName, $projectionId, $data);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    final public function getProjectionName(): string
    {
        return $this->projectionName;
    }

    final public function getProjectionId(): string
    {
        return $this->projectionId;
    }

    final public function getData(): array
    {
        return $this->data;
    }
}