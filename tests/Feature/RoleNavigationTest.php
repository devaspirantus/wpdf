<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function loginAs(User $user): TestResponse
    {
        return $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }

    public function test_role_land_route_returns_the_correct_route_name(): void
    {
        $this->assertSame('reception', Role::reception->landRoute());
        $this->assertSame('dashboard', Role::theory->landRoute());
        $this->assertSame('dashboard', Role::practical->landRoute());
        $this->assertSame('review', Role::review->landRoute());
    }

    public function test_reception_user_is_redirected_to_the_reception_station_on_login(): void
    {
        $user = $this->createUser('reception');

        $this->loginAs($user)
            ->assertRedirect(route('reception'));
    }

    public function test_theory_user_is_redirected_to_the_dashboard_on_login(): void
    {
        $user = $this->createUser('theory');

        $this->loginAs($user)
            ->assertRedirect(route('dashboard'));
    }

    public function test_practical_user_is_redirected_to_the_dashboard_on_login(): void
    {
        $user = $this->createUser('practical');

        $this->loginAs($user)
            ->assertRedirect(route('dashboard'));
    }

    public function test_review_user_is_redirected_to_the_review_station_on_login(): void
    {
        $user = $this->createUser('review');

        $this->loginAs($user)
            ->assertRedirect(route('review'));
    }

    public function test_user_without_role_is_redirected_to_dashboard_on_login(): void
    {
        $user = User::factory()->create();

        $this->loginAs($user)
            ->assertRedirect(route('dashboard'));
    }

    public function test_reception_navigation_shows_only_the_reception_station_link(): void
    {
        $user = $this->createUser('reception');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('محطة الاستقبال')
            ->assertDontSee('بحث عن الحالات')
            ->assertDontSee('محطة المراجعة');
    }

    public function test_theory_navigation_shows_the_theory_station_link(): void
    {
        $user = $this->createUser('theory');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('بدء الاختبار النظري')
            ->assertDontSee('بحث عن الحالات')
            ->assertDontSee('محطة الاستقبال')
            ->assertDontSee('محطة المراجعة');
    }

    public function test_practical_navigation_shows_no_station_link_but_the_pending_table(): void
    {
        $user = $this->createUser('practical');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('بحث عن الحالات')
            ->assertDontSee('محطة الاستقبال')
            ->assertDontSee('محطة المراجعة');
    }

    public function test_review_navigation_shows_only_the_review_station_link(): void
    {
        $user = $this->createUser('review');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('محطة المراجعة')
            ->assertDontSee('محطة الاستقبال')
            ->assertDontSee('بحث عن الحالات');
    }

    public function test_dashboard_shows_the_role_label_and_station_button(): void
    {
        $user = $this->createUser('reception');
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($user->role->label())
            ->assertSee('محطة الاستقبال')
            ->assertSee(route('reception'));
    }

    public function test_dashboard_shows_a_message_for_a_user_without_a_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('غير محدد'))
            ->assertSee(__('لم يتم تحديد محطة لمستخدمك بعد. تواصل مع الإدارة.'));
    }
}
