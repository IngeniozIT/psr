<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception;

use InvalidArgumentException;

class InvalidResource extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct("Invalid resource provided");
    }
}
