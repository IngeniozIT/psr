<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception;

use RuntimeException;

class CannotTellStream extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Could not tell stream");
    }
}
