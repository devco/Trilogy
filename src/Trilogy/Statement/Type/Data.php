<?php

namespace Trilogy\Statement\Type;
use Traversable;

trait Data
{
    private $data = [];

    public function data($data)
    {
        return $this->dataSet([$data]);
    }

    public function dataSet($dataSet)
    {
        foreach ($dataSet as $data) {
            if ($data instanceof Traversable) {
                $data = iterator_to_array($data);
            }

            $this->data[] = $data;
        }

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}