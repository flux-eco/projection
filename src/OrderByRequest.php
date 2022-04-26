<?php

namespace FluxEco\Projection;

use Exception;
use FluxEco\Projection\Core\Domain\Models\OrderBy;

class OrderByRequest
{
    private string $key;
    private string $sortOrder;

    private function __construct(string $key, string $sortOrder)
    {
        $this->key = $key;
        $this->sortOrder = $sortOrder;
    }

    public static function fromArray(?array $orderBy) : ?self {

        if(is_null($orderBy) === true) {
            return null;
        }

        $key = array_key_first($orderBy);
        $sortOrder = $orderBy[$key];
        return new self($key, $sortOrder);
    }

    /**
     * @throws Exception
     */
    public static function new(string $key, string $sortOrder) : self
    {
        return new self($key, $sortOrder);
    }

    public function toDomain(array $schema) : Core\Domain\Models\OrderBy
    {
        $sortOrder = "ASC";
        switch($this->sortOrder) {
            case 'descend':
                $sortOrder = "DESC";
                break;
            case 'ascend':
                $sortOrder = "ASC";
                break;
        }

        return OrderBy::new($this->key, $sortOrder, $schema);
    }

}