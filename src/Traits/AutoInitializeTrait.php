<?php

declare(strict_types=1);

namespace QDM\Traits;

trait AutoInitializeTrait
{
    /**
     * Helper to initialize the model if it has not been initialized yet.
     */
    final protected function qdmAutoInitialize(array &$errors = [], bool $throw = false) : bool
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize($throw)) {
            $this->qdmAppendError(
                of : "errors",
                message: "Could not initialize - declaration errors", 
                to: $errors);
            return false;
        }
        return true;
    }
}
