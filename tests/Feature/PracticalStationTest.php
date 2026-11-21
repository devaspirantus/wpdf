<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\CaseSearch;
use App\Livewire\PracticalStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PracticalStationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['role' => 'practical']);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    private function createPracticalCase(): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => 'سعيد حسن',
            'passport_number' => 'P7654321',
            'ticket_number' => 'T-3001',
            'status' => CaseStatus::practical,
        ]);
    }

    private function storePracticalPhoto(WorkerCase $case): void
    {
        app(StoreCaseDocument::class)->handle(
            $case,
            Station::practical,
            'practical_photo',
            UploadedFile::fake()->image('practical.png'),
            $this->user->id,
        );
    }

    private function saveEvaluation(WorkerCase $case): void
    {
        $scores = [];
        foreach (SaveEvaluation::SECTIONS as $criteria) {
            foreach (array_keys($criteria) as $key) {
                $scores[$key] = 3;
            }
        }

        app(SaveEvaluation::class)->handle($case, 'تصليح المضخة الرئيسية', Difficulty::medium, $scores);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $case = $this->createPracticalCase();

        $this->get('/practical/'.$case->id)->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_the_practical_screen(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $this->get('/practical/'.$case->id)
            ->assertOk()
            ->assertSee('المختبر العملي')
            ->assertSee('سعيد حسن')
            ->assertSee('P7654321')
            ->assertSee('T-3001');
    }

    public function test_the_advance_button_is_disabled_until_the_photo_is_stored(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $component = Livewire::test(PracticalStation::class, ['case' => $case]);

        $component->assertSeeHtml('wire:click="advance" disabled');

        $this->storePracticalPhoto($case);
        $component->dispatch('case-document-stored', caseId: $case->id);

        $component->assertSeeHtml('wire:click="advance" disabled');

        $this->saveEvaluation($case);
        $component->refresh();

        $component->assertDontSeeHtml('wire:click="advance" disabled');
    }

    public function test_advance_moves_a_complete_case_to_review(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $this->storePracticalPhoto($case);
        $this->saveEvaluation($case);

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->call('advance');

        $this->assertDatabaseHas('worker_cases', [
            'id' => $case->id,
            'status' => CaseStatus::review->value,
        ]);
        $this->assertTrue($component->instance()->advancedTo);
    }

    public function test_advance_is_blocked_server_side_and_shows_arabic_error(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->call('advance')
            ->assertSee('المحطة المختبر العملي غير مكتملة: صورة الاختبار العملي، نموذج التقييم');

        $this->assertSame(
            ['صورة الاختبار العملي', 'نموذج التقييم'],
            $component->instance()->missingRequirements,
        );
        $this->assertDatabaseHas('worker_cases', [
            'id' => $case->id,
            'status' => CaseStatus::practical->value,
        ]);
    }

    public function test_case_search_shows_practical_link_for_practical_cases(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', $case->ticket_number)
            ->call('search')
            ->assertSee('الانتقال إلى المختبر العملي');
    }

    public function test_case_search_hides_practical_link_for_other_statuses(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'أحمد',
            'passport_number' => 'P9999999',
            'ticket_number' => 'T-3002',
            'status' => CaseStatus::theory,
        ]);

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', $case->ticket_number)
            ->call('search')
            ->assertDontSee('الانتقال إلى المختبر العملي');
    }
}
