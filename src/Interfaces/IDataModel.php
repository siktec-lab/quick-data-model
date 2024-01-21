<?php

declare(strict_types=1);

namespace QDM\Interfaces;

use Stringable;

interface IDataModel extends Stringable
{
    /**
     * Initialize the data model
     * Most of the time this will be called from the constructor
     * Or automatically when the data model is set, from or extend is called
     */
    public function initialize(bool $throw = true) : bool;

    /**
     * Populate the data model from an object, array or json string
     * each call to from will revert the data model to its default values
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    public function from(object|array|string $data, array &$errors = []) : bool;

    /**
     * Extend the data model from an object, array or json string
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    public function extend(object|array|string $data, array &$errors = []) : bool;

    /**
     * Revert the data model to its default values from latest initialization
     * if no data points are passed it will revert all data points
     */
    public function revert(...$datapoints) : void;

    /**
     * Validate the data model
     * Will perform:
     *  - Required checks
     *  - Custom checks that are defined in the data points
     * 
     * Will not perform:
     *  - Type checks they always performed when setting a value
    */
    public function validate(array &$errors = []) : bool;

    /**
     * Convert the data model to an array
     * @return array<string|int,mixed>
     */
    public function toArray() : array;

    /**
     * Convert the data model to a json string
     * pretty will make the json string with indentation
     * null will be returned if the data model is invalid
     */
    public function toJson(bool $pretty = false) : ?string;

    /**
     * Describe the data model
     *
     * @return array<string,array|string|null> self descrption dictionary
     */
    public function describe(array &$found_nested = []) : array;

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string;
}
