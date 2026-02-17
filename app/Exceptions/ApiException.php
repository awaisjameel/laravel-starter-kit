<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Base exception for API errors.
 * Provides structured error responses for API endpoints.
 */
abstract class ApiException extends Exception
{
    /**
     * @param  string  $message  The error message
     * @param  int  $code  The error code (not HTTP status)
     * @param  array<string, mixed>  $details  Additional error details
     */
    public function __construct(
        string $message = 'An error occurred',
        int $code = 0,
        protected readonly array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code for this exception.
     */
    abstract public function getStatus(): int;

    /**
     * Get the error type identifier.
     */
    final public function getType(): string
    {
        return static::class;
    }

    /**
     * Get additional error details.
     *
     * @return array<string, mixed>
     */
    final public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Render the exception as an HTTP response.
     */
    final public function render(Request $request): JsonResponse
    {
        $data = [
            'success' => false,
            'error' => [
                'type' => $this->getType(),
                'message' => $this->getMessage(),
                'code' => $this->code,
            ],
        ];

        if ($this->details !== []) {
            $data['error']['details'] = $this->details;
        }

        return response()->json($data, $this->getStatus());
    }
}
