<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use Exception;

/**
 * Exception thrown when a category is not found.
 */
class CategoryNotFoundException extends Exception
{
}
