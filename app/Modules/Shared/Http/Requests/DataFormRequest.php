<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Data;

/**
 * @template TData of Data
 */
abstract class DataFormRequest extends FormRequest
{
    /**
     * @return class-string<TData>
     */
    abstract protected function dataClass(): string;

    /**
     * @return TData
     */
    final public function toDto(): Data
    {
        $dataClass = $this->dataClass();

        /** @var TData $data */
        $data = $dataClass::from($this->dtoPayload());

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function dtoPayload(): array
    {
        return $this->validated();
    }
}
