<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception;

use InvalidArgumentException;

class InvalidScheme extends InvalidArgumentException
{
    public static function invalidFirstCharacter(string $scheme): self
    {
        return new self("Invalid scheme '$scheme': first character must be a letter");
    }

    public static function invalidCharacter(string $scheme): self
    {
        return new self("Invalid scheme '$scheme': scheme contains invalid characters");
    }
}
