<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use Exception;
use QDM\Attr\ReferableDataModelAttr;
use QDM\Attr\Checks\With;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Check extends ReferableDataModelAttr
{
    public const DEFAULT_VALUE_POS          = 0;

    public const DEFAULT_BUILTIN            = With::EQUAL;

    public const DEFAULT_BUILTIN_VALUE      = true;

    public const DEFAULT_VALIDATION_MESSAGE = "Not valid";

    /**
     * Execute a check
     *
     * @return array{bool,string} Exec success status and the value or error message
     */
    final public static function execCheck(string|With $callable, array $args) : array
    {
        $message = "";
        $valid = false;

        //Execute:
        if (is_a($callable, With::class, true)) {
            //Exec With builtin:
            $valid = $callable->evaluate(array_shift($args), $args, $message);
        } else {
            //Exec callable:
            try {
                if ($got = $callable(...$args) !== true) {
                    $message = is_string($got) ? $got : self::DEFAULT_VALIDATION_MESSAGE;
                } else {
                    $valid = true;
                }
            } catch (Exception $th) {
                $valid = false;
                $message = "Check failed with internal error";
                // Emit a warning this should not happen:
                trigger_error(
                    sprintf(
                        "Check failed with internal error '%s' in %s on line %d",
                        $th->getMessage(),
                        $th->getFile(),
                        $th->getLine()
                    ),
                    E_USER_WARNING
                );
            }
        }

        return [
            $valid,
            (!$valid && empty($message)) ? self::DEFAULT_VALIDATION_MESSAGE : $message
        ];
    }

    /**
     * Apply checks to a value
     *
     * Will run the given value through the given checks and return true if all checks were applied successfully
     * Otherwise will return false and populate the given errors array
     *
     * @param mixed $value The value to apply checks to
     * @param array<\QDM\Attr\Check> $checks The checks to apply
     * @param array<string> $errors The errors array to populate
     */
    final public static function applyChecks(
        mixed $value,
        array $checks,
        array &$errors = []
    ) : bool {

        $valid = true;
        foreach ($checks as $check) {
            // First priority is the builtin:
            $callable = self::parseWithCheck($check->call);

            // Second priority is the callable:
            if (is_null($callable)) {
                $callable = $check->call;
                if (is_string($check->call) && str_starts_with($check->call, self::SELF_REF)) {
                    $callable = $check->parent_data_model_name . $callable;
                }
                $callable = Check::isCallable($callable);
            }

            // If we still don't have a callable then we have an invalid check:
            if (empty($callable)) {
                $check->qdmAppendError(
                    $check->parent_data_point_name,
                    "Check '{$check->call}' is not callable",
                    $errors
                );
                return false;
            }

            // Apply value to the args array
            $args = Check::applyValueToArgs($value, $check->args);

            // Execute check
            [$status, $message] = Check::execCheck($callable, $args);

            // Check if check was applied successfully
            if (!$status) {
                $check->qdmAppendError(
                    $check->parent_data_point_name,
                    $message,
                    $errors
                );
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Describe the filter
     *
     * return a string representation of the filter definition.
     */
    final public function describe() : string
    {
        return $this->__toString();
    }

    /**
     * A Check definition
     *
     * @param QDM\Attr\Checks\With|string|array<string>|null $call the callable to be used or a builtin.
     * @param array<mixed> $args The extra arguments to be passed to the check callable.
     * @param int $value_pos the position of the value to be checked in the args array defaults to 0.
     * @param string|array<string> $ref a reference to a data point to inherit its check callable.
     */
    public function __construct(
        public With|string|array|null $call = null,
        public array $args = [],
        int $value_pos = 0,
        public string|array|null $ref = null
    ) {
        // Place the value marker in the args array
        if (is_null($ref)) {
            $this->args = self::placeMarkerInArgs($value_pos, $this->args);
        }
    }

    /**
     * Try to parse a With check
     */
    private static function parseWithCheck(With|string|array|null $with) : ?With
    {
        return match (true) {
            is_string($with) => With::tryFrom($with),
            is_a($with, With::class, true) => $with,
            default => null
        };
    }

    /**
     * Describes the Check definition
     */
    final public function __toString() : string
    {
        // Case 1 -> its a reference
        if (!is_null($this->ref)) {
            $ref = is_array($this->ref) ? implode("::", $this->ref) : $this->ref;
            return sprintf("Ref %s", $ref);
        }
        // Case 2 -> its a builtin
        $with = self::parseWithCheck($this->call);
        if (!is_null($with)) {
            return sprintf(
                "@V %s %s",
                $with->value,
                self::argStringable($this->args[1] ?? null)
            );
        }
        // Case 3 -> its a callable
        return match (true) {
            str_starts_with($this->call, self::SELF_REF) => sprintf(
                "%s(%s) is true",
                $this->call,
                self::argStringable($this->args[0] ?? null)
            ),
            self::isCallable($this->call) => sprintf(
                "%s(%s) is true",
                is_array($this->call) ? implode("::", $this->call) : $this->call,
                self::argStringable($this->args[0] ?? null)
            ),
            default => "Invalid Check"
        };
    }
}
