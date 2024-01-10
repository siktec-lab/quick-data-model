<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;

class MediumHuman extends DataModel
{
    #[DataPoint(required: true)]
    public ?string $name = "John Doe";

    #[DataPoint]
    public ?int $age = -1;

    #[DataPoint]
    public ?string $nickname = null;

    #[DataPoint]
    public string|float|int $height = 6.;
}
