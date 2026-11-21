<?php

namespace Tests\Feature;

use App\Actions\ReturnCase;
use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\PracticalStation;
use App\Livewire\ReviewStation;
use App\Livewire\TheoryStation;
use App\Models\CaseReturn;
use App\Models\User;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use LogicException;
use Tests\TestCase;

class ReturnCaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsUser(string $role = 'review'): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    private function createReviewCase(): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => 'عامل الاختبار',
            'passport_number' => 'P1000001',
            'ticket_number' => 'T-5001',
            'status' => CaseStatus::review,
        ]);
    }

    private function storeAllPhotos(WorkerCase $case): void
    {
        $stored = app(StoreCaseDocument::class);

        $stored->handle($case, Station::reception, 'worker_photo', UploadedFile::fake()->image('worker.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'passport', UploadedFile::fake()->image('passport.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'ticket', UploadedFile::fake()->image('ticket.png'), $this->user->id);
        $stored->handle($case, Station::theory, 'theory_photo', UploadedFile::fake()->image('theory.png'), $this->user->id);
        $stored->handle($case, Station::practical, 'practical_photo', UploadedFile::fake()->image('practical.png'), $this->user->id);
    }

    private function createSignedEvaluation(WorkerCase $case): void
    {
        $scores = array_fill(1, 13, 3);
        $scores[1] = 5;

        app(SaveEvaluation::class)->handle(
            $case,
            'صيانة مكيف هواء',
            Difficulty::medium,
            $scores,
        );

        $signature = 'data:image/png;base64,'.base64_encode('fake-signature-bytes');

        app(SignEvaluation::class)->handle($case, $signature, $signature);
    }

    private function returnCase(WorkerCase $case, Station $station, string $note = 'ملاحظة الإرجاع'): CaseReturn
    {
        return app(ReturnCase::class)->handle($case, $station, $note, $this->user->id);
    }

    public function test_return_to_reception_sets_status_and_creates_record(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $return = $this->returnCase($case, Station::reception, 'بيانات ناقصة');

        $this->assertSame(CaseStatus::returned, $case->fresh()->status);
        $this->assertDatabaseCount('case_returns', 1);
        $this->assertDatabaseHas('case_returns', [
            'case_id' => $case->id,
            'returned_to_station' => 'reception',
            'note' => 'بيانات ناقصة',
            'returned_by' => $this->user->id,
        ]);
        $this->assertSame(Station::reception, $return->returned_to_station);
    }

    public function test_return_to_theory_sets_status_and_creates_record(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $return = $this->returnCase($case, Station::theory, 'أخطاء في التقرير النظري');

        $this->assertSame(CaseStatus::returned, $case->fresh()->status);
        $this->assertDatabaseHas('case_returns', [
            'case_id' => $case->id,
            'returned_to_station' => 'theory',
            'note' => 'أخطاء في التقرير النظري',
        ]);
        $this->assertSame(Station::theory, $return->returned_to_station);
    }

    public function test_return_to_practical_clears_evaluation_signatures(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $evaluation = $case->evaluation()->first();
        $this->assertNotNull($evaluation->evaluator_signature);
        $this->assertNotNull($evaluation->worker_signature);
        $this->assertNotNull($evaluation->signed_at);

        $this->returnCase($case, Station::practical, 'إعادة التقييم العملي');

        $this->assertSame(CaseStatus::returned, $case->fresh()->status);

        $evaluation->refresh();
        $this->assertNull($evaluation->evaluator_signature);
        $this->assertNull($evaluation->worker_signature);
        $this->assertNull($evaluation->signed_at);
    }

    public function test_return_to_reception_does_not_clear_evaluation_signatures(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $evaluation = $case->evaluation()->first();

        $this->returnCase($case, Station::reception, 'بيانات ناقصة');

        $evaluation->refresh();
        $this->assertNotNull($evaluation->evaluator_signature);
        $this->assertNotNull($evaluation->worker_signature);
        $this->assertNotNull($evaluation->signed_at);
    }

    public function test_empty_note_throws_logic_exception(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ملاحظة الإرجاع مطلوبة');

        $this->returnCase($case, Station::reception, '   ');
    }

    public function test_return_to_review_throws_logic_exception(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('لا يمكن إرجاع الحالة إلى محطة المراجعة');

        $this->returnCase($case, Station::review, 'ملاحظة');
    }

    public function test_return_from_non_review_status_throws_logic_exception(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'عامل آخر',
            'passport_number' => 'P2000001',
            'ticket_number' => 'T-6001',
            'status' => CaseStatus::theory,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('يمكن الإرجاع فقط من محطة المراجعة');

        $this->returnCase($case, Station::reception, 'ملاحظة');
    }

    public function test_review_station_submit_return_via_livewire(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->set('returnStation', 'theory')
            ->set('returnNote', 'ملاحظة الإرجاع للنظر')
            ->call('submitReturn')
            ->assertHasNoErrors()
            ->assertSee('تم إرجاع الملف بنجاح')
            ->assertSee('الحالات قيد المراجعة');

        $this->assertSame(CaseStatus::returned, $case->fresh()->status);
        $this->assertDatabaseHas('case_returns', [
            'case_id' => $case->id,
            'returned_to_station' => 'theory',
            'note' => 'ملاحظة الإرجاع للنظر',
        ]);
    }

    public function test_review_station_submit_return_validation_error_when_station_missing(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->set('returnStation', '')
            ->set('returnNote', 'ملاحظة')
            ->call('submitReturn')
            ->assertHasErrors(['returnStation' => 'required']);

        $this->assertSame(CaseStatus::review, $case->fresh()->status);
    }

    public function test_return_note_of_exactly_500_chars_passes_validation(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->set('returnStation', 'theory')
            ->set('returnNote', str_repeat('ا', 500))
            ->call('submitReturn')
            ->assertHasNoErrors(['returnNote']);

        $this->assertSame(CaseStatus::returned, $case->fresh()->status);
    }

    public function test_return_note_over_500_chars_fails_validation(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->set('returnStation', 'theory')
            ->set('returnNote', str_repeat('ا', 501))
            ->call('submitReturn')
            ->assertHasErrors(['returnNote' => 'max:500']);

        $this->assertSame(CaseStatus::review, $case->fresh()->status);
    }

    public function test_theory_station_shows_resubmit_banner_when_returned(): void
    {
        $this->actingAsUser('theory');
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $this->returnCase($case, Station::theory, 'ملاحظة الإرجاع النظرية');

        $component = Livewire::test(TheoryStation::class, ['case' => $case]);

        $component->assertSet('isReturned', true);
        $component->assertSee('ملاحظة الإرجاع النظرية');
        $component->assertSee('إرسال للمراجعة');
    }

    public function test_theory_station_resubmit_sets_status_to_review(): void
    {
        $this->actingAsUser('theory');
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $this->returnCase($case, Station::theory, 'ملاحظة');

        $component = Livewire::test(TheoryStation::class, ['case' => $case])
            ->call('resubmit');

        $component->assertSet('isReturned', false);
        $component->assertSet('advancedTo', true);
        $this->assertSame(CaseStatus::review, $case->fresh()->status);
    }

    public function test_practical_station_shows_resubmit_banner_when_returned(): void
    {
        $this->actingAsUser('practical');
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $this->returnCase($case, Station::practical, 'ملاحظة الإرجاع العملي');

        $component = Livewire::test(PracticalStation::class, ['case' => $case]);

        $component->assertSet('isReturned', true);
        $component->assertSee('ملاحظة الإرجاع العملي');
        $component->assertSee('إرسال للمراجعة');
    }

    public function test_practical_station_resubmit_sets_status_to_review(): void
    {
        $this->actingAsUser('practical');
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $this->createSignedEvaluation($case);

        $this->returnCase($case, Station::practical, 'ملاحظة');

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->call('resubmit');

        $component->assertSet('isReturned', false);
        $component->assertSet('advancedTo', true);
        $this->assertSame(CaseStatus::review, $case->fresh()->status);
    }

    public function test_cannot_resubmit_when_not_in_returned_status(): void
    {
        $this->actingAsUser('theory');
        $case = WorkerCase::create([
            'full_name' => 'عامل عادي',
            'passport_number' => 'P3000001',
            'ticket_number' => 'T-7001',
            'status' => CaseStatus::theory,
        ]);

        $this->expectException(LogicException::class);

        app(CaseStateMachine::class)->resubmitToReview($case);
    }

    public function test_review_station_cannot_view_returned_case(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        $this->returnCase($case, Station::theory, 'ملاحظة');

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('الحالات قيد المراجعة')
            ->assertDontSee('عامل الاختبار');
    }
}
