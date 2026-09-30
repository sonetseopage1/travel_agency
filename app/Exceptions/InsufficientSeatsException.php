<?php

namespace App\Exceptions;

use Exception;

class InsufficientSeatsException extends Exception
{
    public function __construct(public readonly int $available)
    {
        parent::__construct("Only {$available} seat(s) remain on this tour.");
    }
}
