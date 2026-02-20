<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use Exception;

/**
 * Exception thrown when attempting to create a category that already exists.
 */
class CategoryAlreadyExistsException extends Exception
{
}
