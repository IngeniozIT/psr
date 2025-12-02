<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception\Message;

use InvalidArgumentException;

class InvalidProtocolVersion extends InvalidArgumentException
{
    public function __construct(string $version)
    {
        parent::__construct("Invalid protocol version: $version");
    }
}
