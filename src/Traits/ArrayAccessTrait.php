<?php

declare(strict_types=1);

namespace QDM\Traits;

trait ArrayAccessTrait
{
    final public function offsetExists(mixed $offset) : bool
    {
        return is_string($offset) ?
            $this->has($offset, export : true) && !is_null($this->{$offset}) :
            false;
    }

    final public function offsetGet(mixed $offset) : mixed
    {
        return $this->get($offset, export : false);
    }

    final public function offsetSet(mixed $offset, mixed $value) : void
    {
        if (is_string($offset)) {
            $this->set($offset, $value, import : false);
        }
    }

    final public function offsetUnset(mixed $offset) : void
    {
        if (is_string($offset)) {
            $this->revert($offset);
        }
    }
}
