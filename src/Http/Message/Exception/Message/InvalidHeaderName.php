<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception\Message;

class InvalidHeaderName extends InvalidHeaderValue
{
    public function __construct(string $headerName)
    {
        parent::__construct("Invalid header name: $headerName");
    }
}
