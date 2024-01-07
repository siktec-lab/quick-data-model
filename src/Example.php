<?php

declare(strict_types=1);

namespace QDM;

/**
 * A Simple Example Class
 */
class Example
{
    /**
     * @param string $name The name to say hello to.
     */
    public function __construct(
        public string $name,
    ) {
    }

    /**
     * Say hello to the name
     */
    public function hello() : string
    {
        return "Hello, {$this->name}";
    }

    /**
     * Just a private method for testing
     */
    private function privateMethod() : void
    {
    }
}
