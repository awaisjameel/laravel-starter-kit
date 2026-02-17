<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Exception for validation errors (422).
 */
final class ValidationError extends ApiException
{
    /**
     * @param  array<string, mixed>  $details  Validation errors by field
     */
    public function __construct(
        string $message = 'Validation failed',
        int $code = 0,
        array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $details, $previous);
    }

    public function getStatus(): int
    {
        return 422;
    }
}
