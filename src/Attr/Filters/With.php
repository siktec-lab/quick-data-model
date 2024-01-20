<?php

namespace QDM\Attr\Filters;

enum With: string {

    // All the filters:
    case EQUAL_STRICT = "===";
    case EQUAL = "==";
    case NOT_EQUAL_STRICT = "!==";
    case NOT_EQUAL = "!=";
    case LESS_THAN_OR_EQUAL = "<=";
    case GREATER_THAN_OR_EQUAL = ">=";
    case LESS_THAN = "<";
    case GREATER_THAN = ">";
    case IS = "is";
    case IS_NOT = "is_not";
    case EQUALS = "equals";
    case NOT_EQUALS = "not_equals";
    case CONTAINS = "contains";
    case MB_CONTAINS = "mb_contains";
    case NOT_CONTAINS = "not_contains";
    case NOT_MB_CONTAINS = "not_mb_contains";
    case STARTS_WITH = "starts_with";
    case MB_STARTS_WITH = "mb_starts_with";
    case STARTS_WITH_CI = "starts_with_ci";
    case MB_STARTS_WITH_CI = "mb_starts_with_ci";
    case NOT_STARTS_WITH = "not_starts_with";
    case MB_NOT_STARTS_WITH = "mb_not_starts_with";
    case NOT_STARTS_WITH_CI = "not_starts_with_ci";
    case MB_NOT_STARTS_WITH_CI = "mb_not_starts_with_ci";
    case ENDS_WITH = "ends_with";
    case MB_ENDS_WITH = "mb_ends_with";
    case ENDS_WITH_CI = "ends_with_ci";
    case MB_ENDS_WITH_CI = "mb_ends_with_ci";
    case NOT_ENDS_WITH = "not_ends_with";
    case MB_NOT_ENDS_WITH = "mb_not_ends_with";
    case NOT_ENDS_WITH_CI = "not_ends_with_ci";
    case MB_NOT_ENDS_WITH_CI = "mb_not_ends_with_ci";
    case LENGTH = "length";
    case MB_LENGTH = "mb_length";
    case NOT_LENGTH = "not_length";
    case NOT_MB_LENGTH = "not_mb_length";
    case MIN_LENGTH = "min_length";
    case MIN_MB_LENGTH = "min_mb_length";
    case MAX_LENGTH = "max_length";
    case MAX_MB_LENGTH = "max_mb_length";
    case BETWEEN_LENGTH = "between_length";
    case BETWEEN_MB_LENGTH = "between_mb_length";
    case NOT_BETWEEN_LENGTH = "not_between_length";
    case NOT_BETWEEN_MB_LENGTH = "not_between_mb_length";
    case IN = "in";
    case NOT_IN = "not_in";
    case IS_NULL = "is_null";
    case IS_NOT_NULL = "is_not_null";
    case IS_EMPTY = "is_empty";
    case IS_NOT_EMPTY = "is_not_empty";
    case TYPE = "type";
    case NOT_TYPE = "not_type";
    case INSTANCE = "instance";
    case NOT_INSTANCE = "not_instance";
    case IN_RANGE = "in_range";
    case NOT_IN_RANGE = "not_in_range";
    case REGEX = "regex";
    case NOT_REGEX = "not_regex";
    case IS_ALPHA = "is_alpha";
    case IS_NOT_ALPHA = "is_not_alpha";
    case IS_ALNUM = "is_alnum";
    case IS_NOT_ALNUM = "is_not_alnum";
    case IS_DIGIT = "is_digit";
    case IS_NOT_DIGIT = "is_not_digit";
    case IS_LOWER = "is_lower";
    case IS_NOT_LOWER = "is_not_lower";
    case IS_UPPER = "is_upper";
    case IS_NOT_UPPER = "is_not_upper";
    case IS_SPACE = "is_space";
    case IS_NOT_SPACE = "is_not_space";
    case IS_XDIGIT = "is_xdigit";
    case IS_NOT_XDIGIT = "is_not_xdigit";   
    case IS_PRINT = "is_print";
    case IS_NOT_PRINT = "is_not_print";
    case IS_GRAPH = "is_graph";
    case IS_NOT_GRAPH = "is_not_graph";

    public function evaluate(mixed $value, array $arg, string &$message = "") : bool
    {
        $arg1 = $arg[0] ?? null;
        $arg2 = $arg[1] ?? null;
        $valid = match ($this) {
            self::EQUAL_STRICT => $value === $arg1,
            self::EQUAL => $value == $arg1,
            self::NOT_EQUAL_STRICT => $value !== $arg1,
            self::NOT_EQUAL => $value != $arg1,
            self::LESS_THAN_OR_EQUAL => $value <= $arg1,
            self::GREATER_THAN_OR_EQUAL => $value >= $arg1,
            self::LESS_THAN => $value < $arg1,
            self::GREATER_THAN => $value > $arg1,
            self::IS => $value === $arg1,
            self::IS_NOT => $value !== $arg1,
            self::EQUALS => $value == $arg1,
            self::NOT_EQUALS => $value != $arg1,
            self::CONTAINS => strpos($value, $arg1) !== false,
            self::MB_CONTAINS => mb_strpos($value, $arg1) !== false,
            self::NOT_CONTAINS => strpos($value, $arg1) === false,
            self::NOT_MB_CONTAINS => mb_strpos($value, $arg1) === false,
            self::STARTS_WITH => strpos($value, $arg1) === 0,
            self::MB_STARTS_WITH => mb_strpos($value, $arg1) === 0,
            self::STARTS_WITH_CI => stripos($value, $arg1) === 0,
            self::MB_STARTS_WITH_CI => mb_stripos($value, $arg1) === 0,
            self::NOT_STARTS_WITH => strpos($value, $arg1) !== 0,
            self::MB_NOT_STARTS_WITH => mb_strpos($value, $arg1) !== 0,
            self::NOT_STARTS_WITH_CI => stripos($value, $arg1) !== 0,
            self::MB_NOT_STARTS_WITH_CI => mb_stripos($value, $arg1) !== 0,
            self::ENDS_WITH => strpos($value, $arg1) === strlen($value) - strlen($arg1),
            self::MB_ENDS_WITH => mb_strpos($value, $arg1) === strlen($value) - strlen($arg1),
            self::ENDS_WITH_CI => stripos($value, $arg1) === strlen($value) - strlen($arg1),
            self::MB_ENDS_WITH_CI => mb_stripos($value, $arg1) === strlen($value) - strlen($arg1),
            self::NOT_ENDS_WITH => strpos($value, $arg1) !== strlen($value) - strlen($arg1),
            self::MB_NOT_ENDS_WITH => mb_strpos($value, $arg1) !== strlen($value) - strlen($arg1),
            self::NOT_ENDS_WITH_CI => stripos($value, $arg1) !== strlen($value) - strlen($arg1),
            self::MB_NOT_ENDS_WITH_CI => mb_stripos($value, $arg1) !== strlen($value) - strlen($arg1),
            self::LENGTH => strlen($value) === $arg1,
            self::MB_LENGTH => mb_strlen($value) === $arg1,
            self::NOT_LENGTH => strlen($value) !== $arg1,
            self::NOT_MB_LENGTH => mb_strlen($value) !== $arg1,
            self::MIN_LENGTH => strlen($value) >= $arg1,
            self::MIN_MB_LENGTH => mb_strlen($value) >= $arg1,
            self::MAX_LENGTH => strlen($value) <= $arg1,
            self::MAX_MB_LENGTH => mb_strlen($value) <= $arg1,
            self::BETWEEN_LENGTH => strlen($value) >= $arg1 && strlen($value) <= $arg2,
            self::BETWEEN_MB_LENGTH => mb_strlen($value) >= $arg1 && mb_strlen($value) <= $arg2,
            self::NOT_BETWEEN_LENGTH => strlen($value) < $arg1 || strlen($value) > $arg2,
            self::NOT_BETWEEN_MB_LENGTH => mb_strlen($value) < $arg1 || mb_strlen($value) > $arg2,
            self::IN => in_array($value, $arg1),
            self::NOT_IN => !in_array($value, $arg1),
            self::IS_NULL => is_null($value),
            self::IS_NOT_NULL => !is_null($value),
            self::IS_EMPTY => empty($value),
            self::IS_NOT_EMPTY => !empty($value),
            self::TYPE => gettype($value) === $arg1,
            self::NOT_TYPE => gettype($value) !== $arg1,
            self::INSTANCE => $value instanceof $arg1,
            self::NOT_INSTANCE => !$value instanceof $arg1,
            self::IN_RANGE => $value >= $arg1 && $value <= $arg2,
            self::NOT_IN_RANGE => $value < $arg1 || $value > $arg2,
            self::REGEX => preg_match($arg1, $value),
            self::NOT_REGEX => !preg_match($arg1, $value),
            self::IS_ALPHA => ctype_alpha($value),
            self::IS_NOT_ALPHA => !ctype_alpha($value),
            self::IS_ALNUM => ctype_alnum($value),
            self::IS_NOT_ALNUM => !ctype_alnum($value),
            self::IS_DIGIT => ctype_digit($value),
            self::IS_NOT_DIGIT => !ctype_digit($value),
            self::IS_LOWER => ctype_lower($value),
            self::IS_NOT_LOWER => !ctype_lower($value),
            self::IS_UPPER => ctype_upper($value),
            self::IS_NOT_UPPER => !ctype_upper($value),
            self::IS_SPACE => ctype_space($value),
            self::IS_NOT_SPACE => !ctype_space($value),
            self::IS_XDIGIT => ctype_xdigit($value),
            self::IS_NOT_XDIGIT => !ctype_xdigit($value),
            self::IS_PRINT => ctype_print($value),
            self::IS_NOT_PRINT => !ctype_print($value),
            self::IS_GRAPH => ctype_graph($value),
            self::IS_NOT_GRAPH => !ctype_graph($value),
            default => false
        };
        if (!$valid) {
            $message = $this->createErrorMessage($arg);
        }
        return $valid;
    }

    public function createErrorMessage(mixed $args) : string 
    {
        $str = match (true) {
            is_array($args) => json_encode($args),
            is_object($args) => get_class($args),
            default => var_export($args, true)
        };
        return match ($this) {
            self::EQUAL_STRICT => "Must be strictly equal to {$str}",
            self::EQUAL => "Must be equal to {$str}",
            self::NOT_EQUAL_STRICT => "Must not be strictly equal to {$str}",
            self::NOT_EQUAL => "Must not be equal to {$str}",
            self::LESS_THAN_OR_EQUAL => "Must be less than or equal to {$str}",
            self::GREATER_THAN_OR_EQUAL => "Must be greater than or equal to {$str}",
            self::LESS_THAN => "Must be less than {$str}",
            self::GREATER_THAN => "Must be greater than {$str}",
            self::IS => "Must be strictly equal to {$str}",
            self::IS_NOT => "Must not be strictly equal to {$str}",
            self::EQUALS => "Must be equal to {$str}",
            self::NOT_EQUALS => "Must not be equal to {$str}",
            self::CONTAINS, self::MB_CONTAINS => "Must contain {$str}",
            self::NOT_CONTAINS, self::NOT_MB_CONTAINS => "Must not contain {$str}",
            self::STARTS_WITH, self::MB_STARTS_WITH => "Must start with {$str}",
            self::STARTS_WITH_CI, self::MB_STARTS_WITH_CI => "Must start with {$str} (case insensitive)",
            self::NOT_STARTS_WITH, self::MB_NOT_STARTS_WITH => "Must not start with {$str}",
            self::NOT_STARTS_WITH_CI, self::MB_NOT_STARTS_WITH_CI => "Must not start with {$str} (case insensitive)",
            self::ENDS_WITH, self::MB_ENDS_WITH => "Must end with {$str}",
            self::ENDS_WITH_CI, self::MB_ENDS_WITH_CI => "Must end with {$str} (case insensitive)",
            self::NOT_ENDS_WITH, self::MB_NOT_ENDS_WITH => "Must not end with {$str}",
            self::NOT_ENDS_WITH_CI, self::MB_NOT_ENDS_WITH_CI => "Must not end with {$str} (case insensitive)",
            self::LENGTH, self::MB_LENGTH => "Must have a length of {$str}",
            self::NOT_LENGTH, self::NOT_MB_LENGTH => "Must not have a length of {$str}",
            self::MIN_LENGTH, self::MIN_MB_LENGTH => "Must have a minimum length of {$str}",
            self::MAX_LENGTH, self::MAX_MB_LENGTH => "Must have a maximum length of {$str}",
            self::BETWEEN_LENGTH, self::BETWEEN_MB_LENGTH => "Must have a length between {$str}",
            self::NOT_BETWEEN_LENGTH, self::NOT_BETWEEN_MB_LENGTH => "Must not have a length between {$str}",
            self::IN => "Must be one of the following values: {$str}",
            self::NOT_IN => "Must not be one of the following values: {$str}",
            self::IS_NULL => "Must be null",
            self::IS_NOT_NULL => "Must not be null",
            self::IS_EMPTY => "Must be empty",
            self::IS_NOT_EMPTY => "Must not be empty",
            self::TYPE => "Must be of type {$str}",
            self::NOT_TYPE => "Must not be of type {$str}",
            self::INSTANCE => "Must be an instance of {$str}",
            self::NOT_INSTANCE => "Must not be an instance of {$str}",
            self::IN_RANGE => "Must be between {$str}",
            self::NOT_IN_RANGE => "Must not be between {$str}",
            self::REGEX => "Must match the following regular expression: {$str}",
            self::NOT_REGEX => "Must not match the following regular expression: {$str}",
            self::IS_ALPHA => "Must be alphabetic",
            self::IS_NOT_ALPHA => "Must not be alphabetic",
            self::IS_ALNUM => "Must be alphanumeric",
            self::IS_NOT_ALNUM => "Must not be alphanumeric",
            self::IS_DIGIT => "Must be a digit",
            self::IS_NOT_DIGIT => "Must not be a digit",
            self::IS_LOWER => "Must be lowercase",
            self::IS_NOT_LOWER => "Must not be lowercase",
            self::IS_UPPER => "Must be uppercase",
            self::IS_NOT_UPPER => "Must not be uppercase",
            self::IS_SPACE => "Must be a space",
            self::IS_NOT_SPACE => "Must not be a space",
            self::IS_XDIGIT => "Must be a hexadecimal digit",
            self::IS_NOT_XDIGIT => "Must not be a hexadecimal digit",
            self::IS_PRINT => "Must be printable",
            self::IS_NOT_PRINT => "Must not be printable",
            self::IS_GRAPH => "Must be visible",
            self::IS_NOT_GRAPH => "Must not be visible",
            default => ""
        };
    }
}
