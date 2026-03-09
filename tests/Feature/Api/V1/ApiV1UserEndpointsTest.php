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

    public function test_admin_users_can_search_and_sort_via_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($admin);

        User::factory()->create([
            'name' => 'Api Alpha',
            'email' => 'api-alpha@example.com',
            'role' => UserRole::User,
        ]);
        User::factory()->create([
            'name' => 'Api Zulu',
            'email' => 'api-zulu@example.com',
            'role' => UserRole::Admin,
        ]);

        $this->getJson('/api/v1/admin/users?search=alpha')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'api-alpha@example.com');

        $this->getJson('/api/v1/admin/users?search=Api&sortBy=name&sortDirection=desc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Api Zulu');
    }

    public function test_admin_users_api_trims_query_input_and_applies_default_query_values(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'created_at' => now()->subHours(2),
        ]);
        Sanctum::actingAs($admin);

        User::factory()->create([
            'name' => 'Api Alice Trimmed',
            'email' => 'api-alice-trimmed@example.com',
            'role' => UserRole::User,
            'created_at' => now()->subMinute(),
        ]);
        User::factory()->create([
            'name' => 'Api Zulu Trimmed',
            'email' => 'api-zulu-trimmed@example.com',
            'role' => UserRole::User,
            'created_at' => now(),
        ]);

        $this->getJson('/api/v1/admin/users?search=%20%20Api%20Alice%20Trimmed%20%20')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'api-alice-trimmed@example.com')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10);

    }

    public function test_admin_users_api_query_validation_errors_are_returned(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users?perPage=1000&page=0&sortBy=invalid&sortDirection=sideways&search='.str_repeat('x', 101))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage', 'page', 'sortBy', 'sortDirection', 'search']);
    }
}
