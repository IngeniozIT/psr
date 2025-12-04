<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception\Uri;

use InvalidArgumentException;

class InvalidPort extends InvalidArgumentException
{
    public function __construct(int $port)
    {
        parent::__construct("Invalid port $port");
    }
}
