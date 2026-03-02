<?php

declare(strict_types=1);

namespace App\Modules\Settings\Data;

use Spatie\LaravelData\Data;

final class ProfileUpdateData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
