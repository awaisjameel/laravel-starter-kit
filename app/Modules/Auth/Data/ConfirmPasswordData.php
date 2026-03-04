<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use Spatie\LaravelData\Data;

final class ConfirmPasswordData extends Data
{
    public function __construct(
        public string $password,
    ) {}
}
