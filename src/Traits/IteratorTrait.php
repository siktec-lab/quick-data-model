<?php

declare(strict_types=1);

namespace QDM\Traits;

trait IteratorTrait
{
    private int $position = 0;

    final public function current() : mixed
    {
        return $this->get($this->data_point_index[$this->position]);
    }

    final public function key() : mixed
    {
        return $this->data_point_index[$this->position];
    }

    final public function next() : void
    {
        ++$this->position;
    }

    final public function rewind() : void
    {
        $this->position = 0;
    }

    final public function valid() : bool
    {
        return isset($this->data_point_index[$this->position]);
    }
}
