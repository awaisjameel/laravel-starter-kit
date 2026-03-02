<?php

declare(strict_types=1);

namespace App\Modules\Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Api\V1\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        return UserResource::make($request->user());
    }
}
