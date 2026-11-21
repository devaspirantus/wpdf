<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\ReviewStation;
use App\Livewire\TheoryStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class StationSummaryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsUser(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    private function createTheoryCase(): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-2001',
            'status' => CaseStatus::theory,
        ]);
    }

    public function test_theory_summary_shows_station_name_role_and_missing_state(): void
    {
        $this->actingAsUser('theory');
        $case = $this->createTheoryCase();

        $component = Livewire::test(TheoryStation::class, ['case' => $case]);

        $component->assertSee('ملخص نهاية المحطة')
            ->assertSee('المختبر النظري')
            ->assertSee('المعمل النظري')
            ->assertSee('0 / 1')
            ->assertSee('ناقص');
    }

    public function test_theory_summary_shows_ready_state_with_next_station(): void
    {
        $this->actingAsUser('theory');
        $case = $this->createTheoryCase();

        app(StoreCaseDocument::class)->handle(
            $case,
            Station::theory,
            'theory_photo',
            UploadedFile::fake()->image('theory.png'),
            $this->user->id,
        );

        $component = Livewire::test(TheoryStation::class, ['case' => $case])
            ->dispatch('case-document-stored', caseId: $case->id);

        $component->assertSee('1 / 1')
            ->assertSee('جاهز للمحطة التالية')
            ->assertSee('المحطة التالية: المختبر العملي');
    }

    public function test_review_summary_is_always_complete(): void
    {
        $this->actingAsUser('review');
        $case = WorkerCase::create([
            'full_name' => 'عامل المراجعة',
            'passport_number' => 'P1000001',
            'ticket_number' => 'T-5001',
            'status' => CaseStatus::review,
        ]);

        $component = Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id);

        $component->assertSee('ملخص نهاية المحطة')
            ->assertSee('مراجعة البيانات')
            ->assertSee('0 / 0')
            ->assertSee('مكتمل')
            ->assertSee('بانتظار توليد PDF');
    }
}
