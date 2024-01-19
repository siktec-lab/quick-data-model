<?php

declare(strict_types=1);

namespace QDM\Attr;

use ReflectionClass;
use ReflectionProperty;
use QDM\Attr\BaseAttr;
use QDM\Interfaces\IDataModel;
use QDM\DataModelException;

/**
 * A base class for referable attributes
 * This class simplifies the process of getting referable attributes
 * With a caching mechanism to avoid having to re-fetch the referable attributes
 *
 * The cache is not a bout the reflection, its about the attributes themselves and "creating" them.
 * Reflection is cheap :
 *      https://gist.github.com/mindplay-dk/3359812?permalink_comment_id=4715685#gistcomment-4715685
 * Creating attributes is not cheap - it requires instantiating the attribute class
 */
abstract class ReferableDataModelAttr extends BaseAttr
{
    /**
     * A special value marker to be used in the args array
     */
    public const VALUE_MARKER = "!VALUE!";

    /**
     * A Shorthand for a reference to a self Class
     */
    public const SELF_REF = "#";

    /**
     * A cache of referable attributes
     *
     * This is used to avoid having to re-fetch the referable attributes for a given class
     * Key: Attribute::class . "::" . Class::class . "::" . Property::class
     * @var array<string,array<\QDM\Attr\ReferableDataModelActionAttribute>> The collected cached Attributes
     */
    protected static $cache = [];

    /**
     * Check if a callable is valid and return the full callable
     *
     * @param string|array<string> $callable
     */
    final public static function isCallable(array|string $callable) : string|bool
    {
        $call_attr = $callable;
        return is_callable($callable, false, $call_attr) ? $call_attr : false;
    }

    /**
     * Replaces the special value marker with the given value inside the args array
     *
     * @return array<mixed> The args array with the value marker replaced
     */
    final public static function applyValueToArgs(mixed $value, array $args) : array
    {
        return array_map(
            fn($arg) => $arg === self::VALUE_MARKER ? $value : $arg,
            $args
        );
    }

    /**
     * Build Attributes for a given property
     */
    final public static function buildAttributes(
        string $attr_type,
        array &$collected,
        ReflectionProperty $property,
        array &$callstack = [] // For circular references detection
    ) : void {

        $reflected_cls  = $property->getDeclaringClass();
        $attributes     = $property->getAttributes($attr_type);
        $collect        = [];

        // Collect the attributes:
        foreach ($attributes as $attribute) {
            $attr = $attribute->newInstance();
            if ($attr->ref) {
                $caller = $reflected_cls->getName() . "::" . $property->getName();
                $attr_type::getRefAttributes(
                    $attr_type,
                    $collect,
                    $reflected_cls,
                    $attr->ref,
                    $callstack,
                    $caller
                );
            } else {
                $collect[] = $attr;
            }
        }

        // Save collected attributes:
        array_push($collected, ...$collect);
    }

    /**
     * Returns the attributes for a given reference
     *
     * @param string $attr_type The type of attribute to get
     * @param array<\QDM\Attr\ReferableDataModelActionAttribute> $collected
     * @param ?ReflectionClass $self The class to get the referable attributes for
     * @param string|array<string>|null $ref Reference definition
     * @param string $caller The caller of this method used for circular references detection
     */
    final public static function getRefAttributes(
        string $attr_type,
        array &$collected = [],
        ?ReflectionClass $self = null,
        string|array|null $ref = null,
        array &$callstack = [], // For circular references detection
        string $caller = "" // For circular references detection
    ) : void {
        // No ref, no referable attributes
        if (is_null($ref)) {
            return;
        }

        // Self ref shorthand:
        if (is_string($ref) && str_starts_with($ref, self::SELF_REF)) {
            $ref = $self->getName() . $ref;
        }

        // Always an array [ class, property(optional) ]
        $ref            = is_string($ref) ? array_filter(explode(self::SELF_REF, $ref)) : $ref;
        $from_class     = $ref[0] ?? null;
        $from_property  = $ref[1] ?? null;

        // No class, no referable attributes
        if (is_null($from_class) || !is_a($from_class, IDataModel::class, true)) {
            throw new DataModelException(
                DataModelException::CODE_REFERABLE_NOT_DATAMODEL,
                [$from_class, $attr_type]
            );
        }

        // No property, no referable attributes
        // TODO: Should implement a way to get referable attributes from top level of a DataModel
        if (!is_null($from_property) && !is_string($from_property)) {
            throw new DataModelException(
                DataModelException::CODE_REFERABLE_NOT_DATAPOINT,
                [$from_property, $attr_type]
            );
        }

        // Check for circular references. Will throw an exception if found otherwise will record the caller
        self::recordCallerCallee($attr_type, $caller, $from_class . "::" . $from_property, $callstack);

        // Check the cache:
        $cache_key = $attr_type . "::" . $from_class . "::" . $from_property;
        if (array_key_exists($cache_key, self::$cache)) {
            array_push($collected, ...self::$cache[$cache_key]);
            return;
        }

        // All good, get the referable attributes:
        $from_class = new \ReflectionClass($from_class);

        // Make sure the property exists:
        if (!$from_class->hasProperty($from_property)) {
            throw new DataModelException(
                DataModelException::CODE_REFERABLE_NOT_DATAPOINT,
                [$from_property, $attr_type]
            );
        }

        $collect = [];
        self::buildAttributes($attr_type, $collect, $from_class->getProperty($from_property), $callstack);

        // Save the referable attributes:
        self::$cache[$cache_key] = $collect;
        array_push($collected, ...$collect);
    }

    /**
     * Record a caller and callee for circular references detection
     *
     * @throws DataModelException if a circular reference is detected
     */
    final protected static function recordCallerCallee(
        string $attr_type,
        string $caller,
        string $callee,
        array &$visited
    ) : void {
        if (array_key_exists($callee . "->" . $caller, $visited)) {
            throw new DataModelException(DataModelException::CODE_CIRCULAR_REFERENCE, [$attr_type, $caller, $callee]);
        }
        $visited[$caller . "->" . $callee] = true;
    }

    /**
     * Place a special value marker in the args array
     *
     * @return array<mixed>
     */
    final protected static function placeMarkerInArgs($position, $args) : array
    {
        // Ensure the position is within the valid range
        $position = max(min($position, count($args)), -count($args) - 1);
        // Insert the marker at the specified position
        array_splice($args, $position, 0, [self::VALUE_MARKER]);
        return $args;
    }
}
