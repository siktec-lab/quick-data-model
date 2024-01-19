<?php

declare(strict_types=1);

namespace QDM;

use Exception;

class DataModelException extends Exception
{
    public const CODE_UNKNOWN_ERROR         = 140;
    public const CODE_ACCESS_MODIFIER       = 141;
    public const CODE_EXTRA_DATAPOINT_TYPE  = 142;
    public const CODE_JSON_SERIALIZE_ERROR  = 143;
    public const CODE_COLLECTION_TYPES      = 144;
    public const CODE_COLLECTION_ITEM_BUILD = 145;
    public const CODE_CIRCULAR_REFERENCE    = 146;
    public const CODE_REFERABLE_NOT_DATAMODEL = 147;
    public const CODE_REFERABLE_NOT_DATAPOINT = 148;

    // MESSAGE CODES TEMPLATES => CODE => MESSAGE, NUMBER OF ARGUMENTS
    private const MESSAGES = [
        self::CODE_UNKNOWN_ERROR         => ['Unknown error', 0],
        self::CODE_ACCESS_MODIFIER       => ["DataPoint '%s' cannot only be 'public' or 'protected'", 1],
        self::CODE_EXTRA_DATAPOINT_TYPE  => ["DataPoint '%s' is marked as extra but is not an 'array'", 1],
        self::CODE_JSON_SERIALIZE_ERROR  => ["JSON serialization error: %s", 1],
        self::CODE_COLLECTION_TYPES      => ["All Collection types must implement IDataModel. Got '%s'", 1],
        self::CODE_COLLECTION_ITEM_BUILD => ["Cannot build a collection item", 0],
        self::CODE_CIRCULAR_REFERENCE    => ["Circular '%s' reference detected between '%s' <-> '%s'", 3],
        self::CODE_REFERABLE_NOT_DATAMODEL => ["Invalid referenced class '%s' in '%s' Attribute", 2],
        self::CODE_REFERABLE_NOT_DATAPOINT => ["Invalid referenced DataPoint '%s' in '%s' Attribute", 2],
    ];

    /**
     * DataModelException constructor.
     *
     * @param int $code 0 will be an unknown error (self::UNKNOWN_ERROR = 140)
     * @param array<mixed> $args
     * @param Exception|null $previous
     * @param string|null $message a custom message for the exception will override the default message
     */
    public function __construct(int $code = 0, array $args = [], ?Exception $previous = null, ?string $message = null)
    {

        // Prepare the message:
        [$code, $message] = $message ?? $this->buildMessage($code, $args);

        // make sure everything is assigned properly
        parent::__construct($message ?? "", $code, $previous);
    }

    // custom string representation of object
    /**
     * Build the message from the code and the arguments
     * @param int $code
     * @param array<mixed> $args
     * @return array{int,string} [code, message]
     */
    private function buildMessage(int $code, array $args = []) : array
    {
        // Code 0 is an unknown error
        $code = $code === 0 ? self::CODE_UNKNOWN_ERROR : $code;
        // Get the message and the number of arguments
        [$message, $num_args] = self::MESSAGES[$code] ?? self::MESSAGES[self::CODE_UNKNOWN_ERROR];
        $apply = [];
        for ($i = 0; $i < $num_args; $i++) {
            $apply[] = $args[$i] ?? "";
        }
        return [$code, sprintf($message, ...$apply)];
    }

    public function __toString() : string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
