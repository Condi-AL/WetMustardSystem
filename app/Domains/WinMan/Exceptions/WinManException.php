<?php

namespace App\Domains\WinMan\Exceptions;

use RuntimeException;

/**
 * Raised for WinMan integration failures such as selecting an MO that no longer
 * exists or has no outstanding quantity.
 */
class WinManException extends RuntimeException
{
}
