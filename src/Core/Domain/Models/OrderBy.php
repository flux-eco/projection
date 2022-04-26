<?php

namespace FluxEco\Projection\Core\Domain\Models;
use Exception;

class OrderBy
{
    private string $key;
    private string $sortOrder;

    private function __construct(string $key, string $sortOrder)
    {
        $this->key = $key;
        $this->sortOrder = $sortOrder;
    }

    /**
     * @throws Exception
     */
    public static function new(string $key, string $sortOrder, array $schema) : self
    {
        /*if(key_exists($key, $schema['properties']) === false) {
            throw new Exception('key does not exist in schema: '.$key);
        }*/

        if($sortOrder != 'ASC' && $sortOrder != 'DESC') {
            throw new Exception('wrong sort order');
        }

        return new self($key, $sortOrder);
    }

    public function toString() : string
    {
        return $this->key . ' ' . $this->sortOrder;
    }
}