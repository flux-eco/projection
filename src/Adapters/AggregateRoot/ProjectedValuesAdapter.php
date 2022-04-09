<?php

namespace FluxEco\Projection\Adapters\AggregateRoot;

use  FluxEco\Projection\Core\Domain;


class ProjectedValuesAdapter
{
    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromJson(string $jsonData): self
    {
        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return new self($data);
    }


    /**
     * @throws \JsonException
     */
    public function toDomain(): Domain\Models\RowValues
    {
        $data = $this->data;
        $items = Domain\Models\RowValues::new();
        //ToDo -> arrayValue should not be our preferred term here...
        foreach ($data as $key => $arrayValue) {
            $value = $arrayValue['value'];
            //ToDo - discuss releations
            if (is_array($value) === true) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }
            $items->offsetSet($key, $value);
        }
        return $items;
    }
}