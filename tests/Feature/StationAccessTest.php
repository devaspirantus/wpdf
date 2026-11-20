<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_user_can_access_the_reception_station(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::reception]));

        $this->get('/reception')->assertOk();
    }

    public function test_theory_user_cannot_access_the_reception_station(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::theory]));

        $this->get('/reception')
            ->assertForbidden()
            ->assertSee('غير مصرح لك بالدخول لهذه المحطة');
    }

    public function test_practical_user_cannot_access_the_reception_station(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::practical]));

        $this->get('/reception')->assertForbidden();
    }

    public function test_review_user_cannot_access_the_reception_station(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::review]));

        $this->get('/reception')->assertForbidden();
    }
}
