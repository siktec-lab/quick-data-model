# Defining a DataModel

## Basic Definition

Each QDM Object must extend the `QDM\DataModel` class.

Let's start with a simple example. Lets say we want to create a DataModel for a Car.

We have this structure:
``` json
{
    "make"  : "Ford",          // A required string
    "model" : "Ford Bronco",   // A required string
    "year"  : 2021,            // A required integer
    "color" : "red",           // An optional string
}
```

We can define this DataModel like this:

``` { .php .annotate .numberLines }
<?php

use QDM\Attr;
use QDM\DataModel;

class Car extends DataModel // (1)!
{
    public function __construct(

        #[Attr\DataPoint(required: true)] // (2)!
        public ?string $make = null,
        
        #[Attr\DataPoint(required: true)] // (3)!
        public ?string $model = null,
        
        #[Attr\DataPoint(required: true)] 
        public ?int $year = null,
        
        #[Attr\DataPoint]
        public ?string $color = null

    ) { }
}

```

1.  We extend the `QDM\DataModel` class. This is required.
2.  Each DataPoint is defined as a `public` or `protected` property with a `#!php-inline #[Attr\DataPoint]` attribute.
3.  A DataPoint inherits the type and default value of the property. In this case, the DataPoint will be a `string` 
and will default to `null`.


!!! note
    This example does not call the parent constructor. This is not required. QDM will 'auto' initialize the 
    DataModel.

    That said, you can call the parent constructor if you want to. It will set the current 'state' (values) of the
    `DataModel` as the initial state. This is useful if you want to 'reset' the `DataModel` to it's initial state.

    Read more about this in the [Initializing a DataModel](#initializing-a-datamodel) section.

That's it! We have defined a DataModel. Now we can create a map data to it

``` php
<?php

// Create a new Car
$car = new Car();

// Import some data:
$car->from([
    "make"  => "Ford",
    "model" => "Ford Bronco",
    "year"  => 2021,
    "color" => "red"
]);

```