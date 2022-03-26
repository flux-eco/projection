<?php

namespace FluxEco\Projection\Core\Domain\Models;

class RowValues implements \IteratorAggregate
{
    private array $items = [];

    private function __construct(array $values = [])
    {
        if (count($values) > 0) {
            foreach ($values as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
    }

    public static function new(): self
    {
        return new self();
    }

    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(string $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet(string $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(string $offset, mixed $value = null)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(string $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}