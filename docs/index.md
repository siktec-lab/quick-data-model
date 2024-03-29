# Welcome to QDM

QDM is a modern PHP 8 Quick Data Model. It is a simple and fast way to create a data model for your PHP project. It is based on [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php) to define the data model. Its main purpose is to serialize and deserialize data from and to JSON. It is also possible to validate the data model. And do some basic data manipulation.

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