# QDM a Modern PHP 8 Quick Data Model

[![Build Status](https://github.com/siktec-lab/quick-data-model/actions/workflows/validate_test.yml/badge.svg?branch=main)](https://github.com/siktec-lab/quick-data-model/actions/workflows/validate_test.yml)

QDM is a modern PHP 8 Quick Data Model. It is a simple and fast way to create a data model for your PHP project. It is based u [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php) to define the data model. Its main purpose is to serialize and deserialize data from and to JSON. It is also possible to validate the data model. And do some basic data manipulation.

## Quick Start
- [Installation](#installation)
- [DOCUMENTATION](https://siktec-lab.github.io/quick-data-model/)
- [A Sneak Peak](#a-sneak-peak)
- [Todo](#todo)

## Installation

```bash
composer require siktec/qdm
```

## A Sneak Peak

```php
<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use QDM\Attr;

/**
 * A Car Data Model
 */
class Car extends QDM\DataModel implements \ArrayAccess
{
    use QDM\Traits\ArrayAccessTrait;

    public bool $is_valid = false;        // Not a DataPoint so for internal use only

    public function __construct(

        #[Attr\DataPoint(required: true)] // This is a required DataPoint can not be null
        public ?string $model = null,

        #[Attr\DataPoint]
        public ?int $year = null,         // A Public DataPoint that will be exported and imported

        #[Attr\DataPoint]
        protected ?string $code = null   // A Protected DataPoint that will imported but not exported
    ) { }
}

/**
 * A Car Collection
 */
#[Attr\Collect(models: Car::class)] // Can also be a "mixed" or an array of types
class CarLot extends QDM\Collection { } // Simple Collection class we can add cars to it

/**
 * A Car Dealership Data Model
 */
class Dealership extends QDM\DataModel implements \ArrayAccess
{
    use QDM\Traits\ArrayAccessTrait; // Bring some more functionality to the DataModel

    #[Attr\DataPoint]
    public ?CarLot $car_lot = null; // A Public DataPoint that will be exported and imported

    public function __construct(

        #[Attr\DataPoint(required: true)] // This is a required DataPoint can not be null
        public ?string $name = null,

        #[Attr\DataPoint(required: true)] // This is a required DataPoint can not be null
        public ?string $address = null,
    )
    {
        $this->car_lot = new CarLot(); //Initialize the CarLot Collection
    }
}

// Create a new Car Dealership
$dealership1 = new Dealership(
    name: "My Car Dealership",
    address: "123 Main St."
);

// Add a new Car to the Car Dealership
$dealership1->car_lot["car_one"] = new Car(
    model: "Ford Bronco",
    year: 2021,
    code: "1234"
);
// OR:
$dealership1->car_lot->add(new Car(
    model: "Ford Limo",
    year: 2021,
    code: "4321"
), "car_two");
// OR:
$dealership1->car_lot->extend([
    "car_three" => [
        "model" => "Ford F150",
        "year" => 2021,
        "code" => "5678"
    ]
]);

// AND MANY MORE WAYS TO PRAGMATICALLY INTERACT WITH THE DATA MODEL

// Export the Car Dealership to JSON (we could also export to an array)
$json1 = $dealership1->toJson(pretty : true); 
echo $json1;
/* 
{
    "name":"My Car Dealership",
    "address":"123 Main St.",
    "car_lot": {
        "car_one":{
            "model":"Ford Bronco",
            "year":2021
        },
        "car_two":{
            "model":"Ford Limo",
            "year":2021
        },
        "car_three":{
            "model":"Ford F150",
            "year":2021
        }
    }
}
*/

// Both ways work the same
$dealership2 = new Dealership();
$validation = [];
// Import the Car Dealership from JSON (we could also import from an array)
$success = $dealership2->from($json1, $validation);
if (!$success) {
    echo "Something went wrong";
    print_r($validation); // This will contain all the errors the basic validation are done by the DataModel class
    exit;
}
if ($json1 === $dealership2->toJson(pretty : true)) {
    echo "They are the same :)";
} else {
    echo "They are different";
}

// Obviously we can also do this:
$dealership3 = new Dealership();
$dealership3->from([ // Import the Car Dealership from an array
    "name" => "My Car Dealership",
    "address" => "123 Main St.",
    "car_lot" => [
        "car_one" => [
            "model" => "Ford Bronco",
            "year" => 2021,
            "code" => "1234"
        ],
        "car_two" => [
            "model" => "Ford Limo",
            "year" => 2021,
            "code" => "4321"
        ],
        "car_three" => [
            "model" => "Ford F150",
            "year" => 2021,
            "code" => "5678"
        ]
    ]
]);

/*
 There a lot more to this library :)
 filters, setters, getters, collections, custom methods for validation, etc.
 Many moreways to interact with the data model such as array access, iterators, etc.
 Many more ways to export and import data from and to the data model.
 SO CHECK OUT THE DOCUMENTATION AND EXAMPLES
*/
```

## Todo
- [x] Add a `DataModel` class to handle the data model
- [x] The `DataModel` class nested data model support
- [x] Safe data model use a $errors array to store all errors instead of throwing exceptions
- [x] DataPoint Attribute support for overriding the default behavior based on the data type and visibility.
- [x] Implement ArrayAccess interface for the DataModel class
- [x] Implement Iterator interface for the DataModel class
- [ ] Add MANY more tests (WIP)
- [x] A General Interface for the DataModel class so Humanity can extend it and create their own DataModel class
- [x] Add a `revert` method to the DataModel class to revert the data model to its original state (default values)
- [x] Add a the ability to manually initialize the data model default state. Basically a don't make the constructor mandatory everthing should work without calling the parent constructor.
- [x] Add a `Collection` class to handle a collection of DataModel objects
- [x] A Collection can also be hosted inside a DataModel and can items of any `IDataModel` type
- [x] The Collection class should implement the ArrayAccess, Iterator and Countable interfaces also.
- [x] The Collection Should Type Check the items added to it and be configurable to allow or not allow types.
- [x] Add more control over how the Collection handles Keys. Especially when adding items to the collection or removing items from the collection.
- [x] Add a `Filter` attribute to the DataModel class to normalize the DataPoint values before they are set.
- [x] Add an option to reference a `Filter` group from DataPoint. This way we can avoid repeating the same filter sequence for multiple DataPoints.
- [x] Add some basic filters that are by default available to the developer. **With the new filter option this is no longer needed as we can call any function**
- [x] Add a `Check` Similar to filter but should return a boolean value. and an optional error message. This is usefull for implementing advanced validation.
- [x] Add a `validate()` method to the DataModel class that will run all the `Check` filters and return an array of errors. Although a `Check` is always invoked before setting a value on a DataPoint this method can be used to validate the entire DataModel after it has been initialized and maybe modified by the user.
- [ ] Add a `Set` Similar to filter this is the very last thing that happens before the value is set so a user can define a custom setter method for the DataPoint maybe its useful for implementing advanced validation or database interaction.
- [ ] Add a `Get` The very last thing that happens before the value is returned this is applied to any data that is exported from the i.e. `toArray, toJson, get()` methods.
- [ ] All features (Filter, Check, Set, Get) Can be applied to the entire DataModel or to a specific DataPoint.
- [x] Add a `describe()` method that will return the parsed data model with all the attributes and their values. useful for debugging especially when using nested data models and references.
- [x] Improve Error|Exception Handling + Tests
- [ ] Documentation (WIP)
- [ ] Examples (WIP)
- [x] `Honey Pot (extra flag)` - A special DataPoint that will store any excess data that is not defined in the data model as a key value pair array.
- [ ] **Release v1.0.0**
- [ ] Add an optional Trait to make a more advanced `toArrayFilter` method available to the DataModel class this will allow the user to define a custom `toArrayFilter` method for the DataModel.
- [ ] Add an optional Trait to make a more advanced `fromArrayFilter` method available to the DataModel class this will allow the user to define a custom `fromArrayFilter` method for the DataModel.
- [ ] When exporting from a Collection to an array the `toArrayFilter` method should be called for each item in the collection.
