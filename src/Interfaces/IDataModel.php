<?php

declare(strict_types=1);

namespace QDM\Interfaces;

use Stringable;

interface IDataModel extends Stringable
{
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
     * Convert the data model to an array
     * @return array<string, mixed>
     */
    public function toArray() : array;

    /**
     * Convert the data model to a json string
     * pretty will make the json string with indentation
     * null will be returned if the data model is invalid
     */
    public function toJson(bool $pretty = false) : ?string;

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string;
}
