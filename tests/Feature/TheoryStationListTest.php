<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Livewire\TheoryStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TheoryStationListTest extends TestCase
{
    use RefreshDatabase;

    private function createCase(CaseStatus $status, string $name, string $ticket): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => $name,
            'passport_number' => 'P-'.str_replace('T-', '', $ticket),
            'ticket_number' => $ticket,
            'status' => $status,
        ]);
    }

    public function test_guests_are_redirected_from_the_list(): void
    {
        $this->get('/theory')->assertRedirect('/login');
    }

    public function test_theory_cases_are_listed_in_the_pending_queue(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $case = $this->createCase(CaseStatus::theory, 'محمود علي', 'T-3001');

        $this->get('/theory')
            ->assertOk()
            ->assertSee('الحالات قيد المعالجة النظرية')
            ->assertSee('محمود علي')
            ->assertSee('T-3001')
            ->assertSeeHtml('wire:click="selectCase('.$case->id.')"', false);
    }

    public function test_non_theory_cases_are_excluded_from_the_pending_queue(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $this->createCase(CaseStatus::reception, 'أحمد', 'T-3002');
        $this->createCase(CaseStatus::practical, 'خالد', 'T-3003');

        $this->get('/theory')
            ->assertOk()
            ->assertDontSee('أحمد')
            ->assertDontSee('خالد');
    }

    public function test_empty_queue_shows_the_no_cases_message(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $this->get('/theory')
            ->assertOk()
            ->assertSee('لا توجد حالات بانتظار المختبر النظري');
    }

    public function test_selecting_a_case_from_the_list_opens_the_processing_view(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $case = $this->createCase(CaseStatus::theory, 'محمود علي', 'T-3004');

        Livewire::test(TheoryStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('محمود علي')
            ->assertSee('T-3004')
            ->assertSee('المحطة التالية');
    }

    public function test_back_to_list_returns_to_the_queue(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $case = $this->createCase(CaseStatus::theory, 'محمود علي', 'T-3005');

        $component = Livewire::test(TheoryStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('محمود علي')
            ->call('backToList');

        $component
            ->assertSee('الحالات قيد المعالجة النظرية')
            ->assertSeeHtml('wire:click="selectCase('.$case->id.')"', false);
        $this->assertNull($component->instance()->case);
    }

    public function test_deep_link_opens_the_processing_view_directly(): void
    {
        $user = User::factory()->create(['role' => 'theory']);
        $this->actingAs($user);

        $case = $this->createCase(CaseStatus::theory, 'سعيد', 'T-3006');

        $this->get('/theory/'.$case->id)
            ->assertOk()
            ->assertSee('سعيد')
            ->assertSee('T-3006')
            ->assertSee('المحطة التالية');
    }
}
