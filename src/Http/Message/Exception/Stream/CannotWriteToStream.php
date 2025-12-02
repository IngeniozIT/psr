<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception\Stream;

use RuntimeException;

class CannotWriteToStream extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Could not write stream");
    }
}
