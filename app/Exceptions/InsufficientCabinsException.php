<?php

namespace App\Exceptions;

/**
 * Raised when a booking cannot be reactivated because the tier it was booked on
 * has no cabins left. Kept separate from InsufficientSeatsException so the admin
 * gets an accurate message instead of a seat count.
 */
class InsufficientCabinsException extends \Exception
{
    public function __construct(string $message = 'Not enough cabins available.')
    {
        parent::__construct($message);
    }
}
