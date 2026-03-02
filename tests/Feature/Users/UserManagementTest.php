<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_user_management_routes(): void
    {
        $testResponse = $this->get('/app/admin/users');

        $testResponse->assertRedirect('/auth/login');
    }

    public function test_non_admin_users_cannot_access_user_listing(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $testResponse = $this->actingAs($user)->get('/app/admin/users');

        $testResponse->assertForbidden();
    }

    public function test_admin_users_can_view_user_listing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->get('/app/admin/users');

        $testResponse->assertOk();
    }

    public function test_admin_users_can_create_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->post('/app/admin/users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'Password123!@#',
            'role' => UserRole::User->value,
        ]);

        $testResponse->assertRedirect('/app/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.com',
            'role' => UserRole::User->value,
        ]);
    }

    public function test_admin_users_cannot_create_invalid_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->post('/app/admin/users', [
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

        $testResponse = $this->actingAs($admin)->put('/app/admin/users/'.$target->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => '',
            'role' => UserRole::Admin->value,
        ]);

        $testResponse->assertRedirect('/app/admin/users');

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

        $testResponse = $this->actingAs($admin)->delete('/app/admin/users/'.$target->id);

        $testResponse->assertRedirect('/app/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_users_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->delete('/app/admin/users/'.$admin->id);

        $testResponse->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_listing_query_parameters_are_validated(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $testResponse = $this->actingAs($admin)->from('/app/admin/users')->get(
            '/app/admin/users?perPage=1000&page=0&sortBy=invalid&sortDirection=sideways&search='.str_repeat('x', 101)
        );

        $testResponse->assertRedirect('/app/admin/users');
        $testResponse->assertSessionHasErrors(['perPage', 'page', 'sortBy', 'sortDirection', 'search']);
    }

    public function test_admin_users_can_search_users_by_name_email_and_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create([
            'name' => 'Alice Search',
            'email' => 'alice@example.com',
            'role' => UserRole::User,
        ]);
        User::factory()->create([
            'name' => 'Bob Search',
            'email' => 'bob@example.com',
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?search=alice')
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->has('users.data', 1)
                ->where('users.data.0.email', 'alice@example.com')
            );

        $this->actingAs($admin)
            ->get('/app/admin/users?search=bob@example.com')
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Bob Search')
            );

        $testResponse = $this->actingAs($admin)->get('/app/admin/users?search=admin');
        $testResponse->assertOk();
        $testResponse->assertSee('Bob Search');
        $testResponse->assertDontSee('Alice Search');
    }

    public function test_admin_users_can_sort_users_by_allowed_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['name' => 'Alpha Sort', 'email' => 'alpha@example.com', 'role' => UserRole::User]);
        User::factory()->create(['name' => 'Zulu Sort', 'email' => 'zulu@example.com', 'role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get('/app/admin/users?search=Sort&sortBy=name&sortDirection=asc')
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->where('users.data.0.name', 'Alpha Sort')
            );

        $this->actingAs($admin)
            ->get('/app/admin/users?search=Sort&sortBy=email&sortDirection=desc')
            ->assertInertia(fn (Assert $assert): Assert => $assert
                ->where('users.data.0.email', 'zulu@example.com')
            );
    }
}
