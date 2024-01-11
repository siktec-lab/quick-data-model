<?php

declare(strict_types=1);

namespace QDM\Traits;

trait IteratorTrait
{
    private int $position = 0;

    final public function current() : mixed
    {
        if ($this instanceof \QDM\DataModel) {
            return $this->get($this->data_point_index[$this->position]);
        }
        // Its a collection:
        return current($this->items);
    }

    final public function key() : mixed
    {
        if ($this instanceof \QDM\DataModel) {
            return $this->data_point_index[$this->position];
        }
        return key($this->items);
    }

    final public function next() : void
    {
        if ($this instanceof \QDM\DataModel) {
            ++$this->position;
            return;
        }
        // Its a collection:
        next($this->items);
    }

    final public function rewind() : void
    {
        if ($this instanceof \QDM\DataModel) {
            $this->position = 0;
            return;
        }
        // Its a collection:
        reset($this->items);
    }

    final public function valid() : bool
    {
        if ($this instanceof \QDM\DataModel) {
            return isset($this->data_point_index[$this->position]);
        }
        // Its a collection: we use array_key_exists instead of isset
        // because we want to allow null values
        return !is_null(key($this->items));
    }
}
