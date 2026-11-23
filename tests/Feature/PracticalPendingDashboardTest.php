<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\PracticalPendingCases;
use App\Models\CaseReturn;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticalPendingDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createPracticalUser(): User
    {
        return User::factory()->create(['role' => 'practical']);
    }

    private function createPendingCase(array $overrides = []): WorkerCase
    {
        return WorkerCase::create(array_merge([
            'full_name' => 'عبدالله حسن',
            'passport_number' => 'P7654321',
            'ticket_number' => 'T-2001',
            'status' => CaseStatus::practical,
        ], $overrides));
    }

    public function test_practical_user_sees_the_pending_cases_table_on_the_dashboard(): void
    {
        $user = $this->createPracticalUser();
        $this->createPendingCase();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('طلبات المعمل العملي')
            ->assertSee('عبدالله حسن')
            ->assertSee('T-2001')
            ->assertSee('P7654321');
    }

    public function test_other_roles_do_not_see_the_practical_pending_table(): void
    {
        foreach (['reception', 'theory', 'review'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('طلبات المعمل العملي');
        }
    }

    public function test_dashboard_shows_an_empty_state_when_no_cases_are_pending(): void
    {
        $user = $this->createPracticalUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('لا توجد طلبات محولة حاليا للمعمل العملي.');
    }

    public function test_returned_to_practical_cases_appear_with_a_returned_badge(): void
    {
        $user = $this->createPracticalUser();
        $reviewer = $this->createPracticalUser();
        $case = $this->createPendingCase(['status' => CaseStatus::returned]);

        CaseReturn::create([
            'case_id' => $case->id,
            'returned_to_station' => Station::practical,
            'note' => 'ملاحظة الإرجاع',
            'returned_by' => $reviewer->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('طلبات المعمل العملي')
            ->assertSee($case->full_name)
            ->assertSee('مُرجع');
    }

    public function test_cases_returned_to_other_stations_do_not_appear(): void
    {
        $user = $this->createPracticalUser();
        $reviewer = $this->createPracticalUser();
        $this->createPendingCase([
            'ticket_number' => 'T-2002',
            'status' => CaseStatus::returned,
        ]);

        CaseReturn::create([
            'case_id' => WorkerCase::where('ticket_number', 'T-2002')->first()->id,
            'returned_to_station' => Station::theory,
            'note' => 'ملاحظة الإرجاع',
            'returned_by' => $reviewer->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('T-2002', escape: false);
    }

    public function test_search_filters_by_ticket_number(): void
    {
        $user = $this->createPracticalUser();
        $this->actingAs($user);

        $this->createPendingCase(['ticket_number' => 'T-2001']);
        $this->createPendingCase([
            'ticket_number' => 'T-2002',
            'full_name' => 'سعيد محمد',
            'passport_number' => 'P5555555',
        ]);

        Livewire::test(PracticalPendingCases::class)
            ->set('search', 'T-2001')
            ->assertSee('T-2001', escape: false)
            ->assertDontSee('T-2002', escape: false);
    }

    public function test_search_filters_by_passport_number(): void
    {
        $user = $this->createPracticalUser();
        $this->actingAs($user);

        $this->createPendingCase(['passport_number' => 'P-XXX']);
        $this->createPendingCase([
            'passport_number' => 'P-YYY',
            'full_name' => 'سعيد محمد',
            'ticket_number' => 'T-3002',
        ]);

        Livewire::test(PracticalPendingCases::class)
            ->set('search', 'P-XXX')
            ->assertSee('P-XXX', escape: false)
            ->assertDontSee('P-YYY', escape: false);
    }
}
