<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Exception for resource not found errors (404).
 */
final class NotFoundError extends ApiException
{
    public function __construct(
        string $message = 'Resource not found',
        int $code = 0,
        array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $details, $previous);
    }

    public function getStatus(): int
    {
        return 404;
    }
}
