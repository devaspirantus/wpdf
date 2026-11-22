<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Livewire\TheoryPendingCases;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TheoryPendingDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createTheoryUser(): User
    {
        return User::factory()->create(['role' => 'theory']);
    }

    private function createPendingCase(array $overrides = []): WorkerCase
    {
        return WorkerCase::create(array_merge([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::theory,
        ], $overrides));
    }

    public function test_theory_user_sees_the_pending_cases_table_on_the_dashboard(): void
    {
        $user = $this->createTheoryUser();
        $this->createPendingCase();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('طلبات المعمل النظري')
            ->assertSee('محمود علي')
            ->assertSee('T-1001')
            ->assertSee('P1234567');
    }

    public function test_other_roles_do_not_see_the_theory_pending_table(): void
    {
        foreach (['reception', 'practical', 'review'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('طلبات المعمل النظري');
        }
    }

    public function test_dashboard_shows_an_empty_state_when_no_cases_are_pending(): void
    {
        $user = $this->createTheoryUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('لا توجد طلبات محولة حالياً من الاستقبال.');
    }

    public function test_search_filters_by_ticket_number(): void
    {
        $user = $this->createTheoryUser();
        $this->actingAs($user);

        $this->createPendingCase(['ticket_number' => 'T-1001']);
        $this->createPendingCase([
            'ticket_number' => 'T-1002',
            'full_name' => 'أحمد سعيد',
            'passport_number' => 'P9999999',
        ]);

        Livewire::test(TheoryPendingCases::class)
            ->set('search', 'T-1001')
            ->assertSee('T-1001', escape: false)
            ->assertDontSee('T-1002', escape: false);
    }

    public function test_search_filters_by_passport_number(): void
    {
        $user = $this->createTheoryUser();
        $this->actingAs($user);

        $this->createPendingCase(['passport_number' => 'P-AAA']);
        $this->createPendingCase([
            'passport_number' => 'P-BBB',
            'full_name' => 'أحمد سعيد',
            'ticket_number' => 'T-2002',
        ]);

        Livewire::test(TheoryPendingCases::class)
            ->set('search', 'P-AAA')
            ->assertSee('P-AAA', escape: false)
            ->assertDontSee('P-BBB', escape: false);
    }
}
