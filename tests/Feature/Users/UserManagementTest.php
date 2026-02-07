<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_user_management_routes(): void
    {
        $testResponse = $this->get('/users');

        $testResponse->assertRedirect('/login');
    }

    public function test_non_admin_users_cannot_access_user_listing(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $testResponse = $this->actingAs($user)->get('/users');

        $testResponse->assertForbidden();
    }

    public function test_admin_users_can_view_user_listing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->get('/users');

        $testResponse->assertOk();
    }

    public function test_admin_users_can_create_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'Password123!@#',
            'role' => UserRole::User->value,
        ]);

        $testResponse->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.com',
            'role' => UserRole::User->value,
        ]);
    }

    public function test_admin_users_cannot_create_invalid_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->post('/users', [
            'name' => '',
            'email' => 'invalid',
            'password' => 'weak',
            'role' => 'invalid-role',
        ]);

        $testResponse->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_admin_users_can_update_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        $testResponse = $this->actingAs($admin)->put('/users/'.$target->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => '',
            'role' => UserRole::Admin->value,
        ]);

        $testResponse->assertRedirect('/users');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_admin_users_can_delete_other_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        $testResponse = $this->actingAs($admin)->delete('/users/'.$target->id);

        $testResponse->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_users_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->delete('/users/'.$admin->id);

        $testResponse->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_listing_query_parameters_are_validated(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->from('/users')->get('/users?perPage=1000&page=0');

        $testResponse->assertRedirect('/users');
        $testResponse->assertSessionHasErrors(['perPage', 'page']);
    }
}
