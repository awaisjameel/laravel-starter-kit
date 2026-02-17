<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Exception for unauthorized access errors (403).
 */
final class UnauthorizedError extends ApiException
{
    public function __construct(
        string $message = 'Unauthorized action',
        int $code = 0,
        array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $details, $previous);
    }

    public function getStatus(): int
    {
        return 403;
    }
}
