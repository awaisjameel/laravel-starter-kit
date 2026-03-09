<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Users\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Tests\TestCase;

final class ModulePolicyDiscoveryTest extends TestCase
{
    public function test_module_policies_are_registered_by_convention(): void
    {
        $policy = app(Gate::class)->getPolicyFor(User::class);

        $this->assertInstanceOf(UserPolicy::class, $policy);
    }
}
