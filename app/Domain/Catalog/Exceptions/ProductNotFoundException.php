<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use Exception;

/**
 * Exception thrown when a product is not found.
 */
class ProductNotFoundException extends Exception
{
}
