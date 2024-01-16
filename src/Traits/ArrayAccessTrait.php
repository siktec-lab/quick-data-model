<?php

declare(strict_types=1);

namespace QDM\Traits;

use QDM\DataModel;

trait ArrayAccessTrait
{
    final public function offsetExists(mixed $offset) : bool
    {
        if ($this instanceof DataModel) {
            return is_string($offset) ?
                $this->has($offset, export : false) && !is_null($this->{$offset}) :
                false;
        }
        // Its a collection:
        return is_int($offset) || is_string($offset) ? $this->has($offset) : false;
    }

    final public function offsetGet(mixed $offset) : mixed
    {
        if ($this instanceof DataModel) {
            return is_string($offset) ? $this->get($offset, export : false) : null;
        }
        // Its a collection:
        return is_int($offset) || is_string($offset) ? $this->get($offset) : null;
    }

    final public function offsetSet(mixed $offset, mixed $value) : void
    {
        if ($this instanceof DataModel) {
            if (is_string($offset)) {
                $this->set($value, $offset, import : false);
            }
            return;
        }
        // Its a collection:
        if (is_int($offset) || is_string($offset) || is_null($offset)) {
            $this->set($value, $offset);
        }
    }

    final public function offsetUnset(mixed $offset) : void
    {
        if ($this instanceof DataModel) {
            if (is_string($offset)) {
                $this->revert($offset);
            }
            return;
        }
        // Its a collection:
        if (is_string($offset) || is_int($offset)) {
            $this->clear($offset);
        }
    }
}
