<?php

declare(strict_types=1);

namespace QDM\Traits;

use QDM\DataModelException;

trait AppendErrorTrait
{

    /**
     * Error reporting helper
     * $type can be 
     */
    final protected function qdmAppendError(
        string|null $of = "errors", 
        string|array $message = "", 
        array &$to = []
    ) : void {   
        // The name initialization:
        $to[$of] = $to[$of] ?? [];
        if (is_array($message)) {
            $to[$of] = array_merge($to[$of], $message);
            return;
        }
        $to[$of][] = $message;
    }

}
