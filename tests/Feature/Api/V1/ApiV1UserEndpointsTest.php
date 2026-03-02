<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ApiV1UserEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_me_endpoint(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_authenticated_users_can_access_me_endpoint(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_non_admin_users_cannot_access_admin_users_api(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_admin_users_can_manage_users_via_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);

        $testResponse = $this->postJson('/api/v1/admin/users', [
            'name' => 'Api User',
            'email' => 'api-user@example.com',
            'password' => 'Password123!@#',
            'role' => UserRole::User->value,
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.email', 'api-user@example.com');

        /** @var int $createdUserId */
        $createdUserId = $testResponse->json('data.id');

        $this->putJson('/api/v1/admin/users/'.$createdUserId, [
            'name' => 'Api User Updated',
            'email' => 'api-user-updated@example.com',
            'password' => '',
            'role' => UserRole::Admin->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.email', 'api-user-updated@example.com');

        $this->deleteJson('/api/v1/admin/users/'.$createdUserId)->assertNoContent();
    }

    public function test_admin_user_create_validation_errors_are_returned(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/admin/users', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'role' => 'invalid-role',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }
}
