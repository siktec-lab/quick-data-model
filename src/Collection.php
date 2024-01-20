<?php

declare(strict_types=1);

namespace QDM;

use ArrayAccess;
use Countable;
use Iterator;
use ReflectionClass;
use Exception;
use QDM\Attr;
use QDM\Traits;
use QDM\Interfaces\IDataModel;

class Collection implements IDataModel, Countable, ArrayAccess, Iterator
{
    use Traits\SafeJsonTrait;
    use Traits\AppendErrorTrait;
    use Traits\ArrayAccessTrait;
    use Traits\IteratorTrait;

    protected bool $is_initialized  = false;

    /**
     * @var array<string> The supported types for this collection
     */
    protected array $types = [];

    /**
     * @var array<string|int,QDM\Interfaces\IDataModel> The collection items
     */
    protected array $items          = [];

    /**
     * @var bool Should the collection re-index when items are removed
     */
    protected bool $re_indexing    = true;

    /**
     * @var Attr\Collect|null The collection attribute
     */
    private ?Attr\Collect $collect  = null;

    /**
     * Number of DataModels in the collection
     */
    final public function count() : int
    {
        return count($this->items);
    }

    /**
     * Check if a DataModel exists in the collection based on its key
     */
    final public function has(string|int $key) : bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Add a DataModel to the collection
     *
     * Only adds if the key does not exist If the key is null it will be appended.
    */
    final public function add(
        array|string|IDataModel $value,
        string|int|null $key = null,
        array &$errors = [],
        bool $clone = true
    ) : bool {
        if ($this->has($key)) {
            $this->qdmAppendError(message : "Key '{$key}' already exists", to : $errors);
            return false;
        }
        return $this->set($value, $key, $errors, $clone);
    }

    /**
     * Set a DataModel in the collection
     *
     * If the key is null it will be appended with an integer key. This method will overwrite
     * an existing item with the same key
     */
    final public function set(
        array|string|IDataModel $value,
        string|int|null $key = null,
        array &$errors = [],
        bool $clone = true
    ) : bool {

        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            $this->qdmAppendError(
                message : "Collection could not be initialized declaration error",
                to : $errors
            );
            return false;
        }

        // Process the value:
        $type = $this->types[0] ?? null;
        switch (gettype($value)) {
            case "object":
                if (!$this->isTypeValid($value)) {
                    $this->qdmAppendError(
                        message : "Value of '{$key}' is not supported by this collection type",
                        to : $errors
                    );
                    return false;
                }
                $type = get_class($value);
                break;
            default:
                // If we have multiple types only allow objects:
                if (count($this->types) !== 1 || is_null($type)) {
                    $this->qdmAppendError(
                        message : "Collection has multiple types and only objects are allowed",
                        to : $errors
                    );
                    return false;
                }
                $clone = true;
                break;
        }

        // Build the item:
        $item = $clone ? $this->buildItem($type, $value, $errors) : $value;
        if (is_null($item)) {
            $this->qdmAppendError(
                message : "Could not build collection item",
                to : $errors
            );
            return false;
        }

        // Set the item:
        if (is_null($key)) {
            $this->items[] = $item;
        } else {
            $this->items[$key] = $item;
        }

        return true;
    }

    /**
     * Revert collection items to default values. If no keys are passed it will revert all items.
     *
     * This will not revert the collection itself or nested collections it will only revert
     * the items and only a full revert. If you want to revert a specific item use the revert method on the item.
     * If you want to clear the collection use the 'clear' method.
     */
    final public function revert(...$keys) : void
    {
        $keys = empty($keys) ? array_keys($this->items) : $keys;
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->items)) {
                $this->items[$key]->revert();
            }
        }
    }

    /**
     * Stops a list like collection from re-indexing when items are removed
     *
     * A list like collection means the keys are integers and sequential then the collection will be re-indexed
     * to keep the keys sequential. This is the default behavior.
     * If you do not want this behavior you can disable it by passing false.
     * If you want to check the current value you can pass null.
     * This can be useful if you want to remove items from a list in a loop and you do not want to re-index
     * the collection every time or mess up the loop.
     */
    final public function autoReIndexing(?bool $auto = null) : bool
    {
        if (!is_null($auto)) {
            $this->re_indexing = $auto;
        }
        return $this->re_indexing;
    }

    /**
     * Check if the collection is a list (meaning the keys are integers and sequential)
     */
    final public function isList() : bool
    {
        return array_is_list($this->items);
    }

    /**
     * Check if the collection is a map (associative array)
     */
    final public function isMap() : bool
    {
        return !$this->isList();
    }

    /**
     * Convert the collection to a list
     *
     * This will re-index the collection keys to be sequential integers
     * All keys will be lost and the collection will be a list.
     */
    final public function convertToList() : void
    {
        $this->items = $this->getValues();
    }

    /**
     * Convert the collection to a map
     *
     * This will re-index the collection keys to be associative strings
     * You can pass a prefix to be added to the keys e.g. "num_" will result in "num_0", "num_1", etc.
     */
    final public function convertToMap(string $prefix = "") : void
    {
        $this->items = array_combine(
            array_map(function ($key) use ($prefix) {
                return $prefix . $key;
            }, $this->getKeys()),
            $this->getValues()
        );
    }

    /**
     * Initialize the collection
     *
     * Will initialize the collection and populate the types it supports
     * @throws DataModelException if the collection is not defined properly
     */
    final public function initialize(bool $throw = true) : bool
    {
        try {
            $this->defineCollection();
            $this->is_initialized = true;
        } catch (DataModelException $e) {
            if ($throw) {
                throw $e;
            }
            return false;
        } catch (Exception $e) {
            if ($throw) {
                throw new DataModelException(DataModelException::CODE_UNKNOWN_ERROR, [$e->getMessage()], $e);
            }
            return false;
        }
        return true;
    }

    /**
     * Populate the collection from an object, array or json string
     *
     * This will clear the collection and populate it with the new data
     * Only Collection can populate a collection. If you want to add a single item use the 'add' method
     * or if you want to set a single item use the 'set' method.
     */
    final public function from(object|array|string $data, array &$errors = []) : bool
    {
        // Reset the data model:
        $this->clear();
        // Now extend the data model:
        return $this->extend($data, $errors);
    }

    /**
     * Extend the collection from an object, array or json string
     *
     * Only a Collection can extend a collection. If you want to add a single item use the 'add' method
     * or if you want to set a single item use the 'set' method.
     */
    final public function extend(object|array|string $data, array &$errors = []) : bool
    {
        if (is_array($data)) {
            return $this->fromArray($data, $errors);
        }
        if ($data instanceof Collection) {
            // We want to loop through the items and set them:
            foreach ($data as $key => $item) {
                $this->set($item, $key, $errors, true);
            }
            return $this->fromArray($data->toArray(), $errors);
        }
        if (is_string($data)) {
            $data = $this->jsonDecodeCatch($data, assoc: true);
            if (is_array($data)) {
                return $this->fromArray($data, $errors);
            }
        }
        $this->qdmAppendError(
            message : "Collection item is not of a valid type",
            to : $errors
        );
        return false;
    }

    /**
     * Remove elements from the collection. If no keys are passed it will clear the entire collection
     *
     * This method is also used by the ArrayAccessTrait unset method.
     * If the collection is a list then the keys will be re-indexed. If you do not want this behavior
     * you can disable it by calling the autoReIndexing method.
     */
    final public function clear(...$keys) : void
    {
        $keys = empty($keys) ? array_keys($this->items) : $keys;
        $removed = false;
        $is_list = array_is_list($this->items);
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->items)) {
                unset($this->items[$key]);
                $removed = true;
            }
        }
        // If its a list then we need to reindex the array:
        if ($this->re_indexing && $is_list && $removed) {
            $this->convertToList();
        }
    }

    /**
     * Get a Collection item by key name
     */
    final public function get(string|int $name) : IDataModel|null
    {
        return $this->items[$name] ?? null;
    }

    /**
     * Get the collection keys
     * @return array<mixed>
     */
    final public function getKeys() : array
    {
        return array_keys($this->items);
    }

    /**
     * Get the collection underlying values
     * @return array<QDM\Interfaces\IDataModel>
     */
    final public function getValues() : array
    {
        return array_values($this->items);
    }

    /**
     * Convert the collection to an array
     *
     * This will convert the collection to an array of arrays while converting the items to arrays
     * recursively.
     * @return array<string|int,mixed>
     */
    final public function toArray() : array
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            return [];
        }
        // Build the array:
        $data = [];
        foreach ($this->items as $key => $value) {
            $data[$key] = $value->toArray();
        }
        return $data;
    }

    /**
     * Convert the collection to a json string
     *
     * pretty will make the json string with indentation
     * null will be returned if the collection is invalid
     */
    final public function toJson(bool $pretty = false) : ?string
    {
        return $this->jsonEncodeCatch($this->toArray(), $pretty ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Describe the collection
     *
     * @param array<string> $found_nested The nested data models found
     * @return array<string,array|string|null> self descrption dictionary
     */
    final public function describe(array &$found_nested = []) : array
    {
        return [
            "name"   => "QDM\Collection",
            "items" => implode("|", $this->types)
        ];
    }

    /**
     * Populate the collection from an array
     *
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    final protected function fromArray(array $data, array &$errors = []) : bool
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            $this->qdmAppendError(
                message : "Collection could not be initialized declaration error",
                to : $errors
            );
            return false;
        }
        $init_errors = count($errors);
        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $this->set($value, null, $errors);
            } else {
                $this->set($value, $key, $errors);
            }
        }
        return count($errors) == $init_errors;
    }

    /**
     * Create a new collection
     *
     * You can pass an array, object or json string to populate the collection
     * This is the same as calling 'from' on the collection
     *
     * @throws DataModelException if the collection is not defined properly
     */
    public function __construct(array|string|object $data = [], array &$errors = [])
    {
        $this->initialize(true);
        if (!empty($data)) {
            $this->from($data, $errors);
        }
    }

    /**
     * Determine which type of collection this is
     *
     * @throws DataModelException if the collection is not defined properly
     */
    private function defineCollection() : void
    {
        //TODO: this should move to the Attr\Collect attribute
        // Get the collection attribute:
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Attr\Collect::class) {
                $this->collect = $attribute->newInstance();
                $types = $this->collect->models;
                $this->types = is_array($types) ? $types : [$types];
            }
        }
        //If mixed then we are done
        if (in_array("mixed", $this->types)) {
            $this->types = [];
        }
        // Validate types are all IDataModel
        foreach ($this->types as $type) {
            if (!is_subclass_of($type, IDataModel::class)) {
                throw new DataModelException(
                    DataModelException::CODE_COLLECTION_TYPES,
                    [$type]
                );
            }
        }
    }

    /**
     * Check if the type is valid for this collection
     */
    private function isTypeValid(object|string $value) : bool
    {
        //TODO: this should move to the Attr\Collect attribute
        if (empty($this->types) && is_a($value, IDataModel::class)) {
            return true;
        }
        foreach ($this->types as $type) {
            if (is_a($value, $type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Populate a specific data model from an object, array or json string
     *
     * This assumes the type is valid and will not perform any type checking
     */
    private function buildItem(string $type, array|string|IDataModel $item, array &$errors = []) : ?IDataModel
    {
        try {
            $reflection = new ReflectionClass($type);
            $instance = $reflection->newInstance();
            $status = $instance->from($item, $errors);
            return $status ? $instance : null;
        } catch (Exception $e) {
            // Only raise a warning if the error is not a declaration error:
            trigger_error(
                sprintf(
                    "Could not build item: %s in %s on line %d",
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                E_USER_WARNING
            );
        }
        return null;
    }

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string
    {
        return $this->toJson();
    }
}
