<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_be_created_with_each_of_the_four_roles_and_the_value_persists(): void
    {
        foreach (Role::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertSame($role, $user->fresh()->role);
        }
    }

    public function test_reading_user_role_returns_a_role_enum_instance(): void
    {
        $user = User::factory()->create(['role' => Role::practical]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertSame(Role::practical, $user->role);
    }

    public function test_creating_a_user_with_an_invalid_role_value_throws_a_value_error(): void
    {
        $this->expectException(\ValueError::class);

        User::factory()->create(['role' => 'not-a-role']);
    }
}
