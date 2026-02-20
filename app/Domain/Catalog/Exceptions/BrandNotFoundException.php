<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use Exception;

/**
 * Exception thrown when a brand is not found.
 */
class BrandNotFoundException extends Exception
{
}
