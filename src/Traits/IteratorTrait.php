<?php

declare(strict_types=1);

namespace QDM\Traits;

use QDM\DataModel;

trait IteratorTrait
{
    private int $qdm_iter_position = 0;

    final public function current() : mixed
    {
        if ($this instanceof DataModel) {
            return $this->get($this->qdm_data_point_index[$this->qdm_iter_position]);
        }
        // Its a collection:
        return current($this->items);
    }

    final public function key() : mixed
    {
        if ($this instanceof DataModel) {
            return $this->qdm_data_point_index[$this->qdm_iter_position];
        }
        return key($this->items);
    }

    final public function next() : void
    {
        if ($this instanceof DataModel) {
            ++$this->qdm_iter_position;
            return;
        }
        // Its a collection:
        next($this->items);
    }

    final public function rewind() : void
    {
        if ($this instanceof DataModel) {
            $this->qdm_iter_position = 0;
            return;
        }
        // Its a collection:
        reset($this->items);
    }

    final public function valid() : bool
    {
        if ($this instanceof DataModel) {
            return isset($this->qdm_data_point_index[$this->qdm_iter_position]);
        }
        // Its a collection: we use array_key_exists instead of isset
        // because we want to allow null values
        return !is_null(key($this->items));
    }
}
