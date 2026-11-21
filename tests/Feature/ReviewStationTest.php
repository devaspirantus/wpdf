<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\ReviewStation;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewStationTest extends TestCase
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
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-2001',
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

    private function createSignedEvaluation(WorkerCase $case): Evaluation
    {
        $scores = array_fill(1, 13, 3);
        $scores[1] = 5;

        $evaluation = app(SaveEvaluation::class)->handle(
            $case,
            'صيانة مكيف هواء',
            Difficulty::medium,
            $scores,
        );

        $signature = 'data:image/png;base64,'.base64_encode('fake-signature-bytes');

        app(SignEvaluation::class)->handle($case, $signature, $signature);

        return $evaluation->fresh();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/review')->assertRedirect('/login');
    }

    public function test_user_of_wrong_role_cannot_access_review_station(): void
    {
        $this->actingAsUser('reception');

        $this->get('/review')->assertForbidden();
    }

    public function test_review_user_sees_only_review_status_cases(): void
    {
        $this->actingAsUser();
        $underReview = $this->createReviewCase();
        $underTheory = WorkerCase::create([
            'full_name' => 'عنوان آخر',
            'passport_number' => 'P9999999',
            'ticket_number' => 'T-9999',
            'status' => CaseStatus::theory,
        ]);

        $this->get('/review')
            ->assertOk()
            ->assertSee('محمود علي')
            ->assertSee('T-2001')
            ->assertDontSee('عنوان آخر')
            ->assertDontSee('T-9999');
    }

    public function test_empty_review_queue_shows_arabic_notice(): void
    {
        $this->actingAsUser();

        $this->get('/review')
            ->assertOk()
            ->assertSee('لا توجد حالات بانتظار المراجعة');
    }

    public function test_select_case_shows_photos_and_signed_evaluation(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $evaluation = $this->createSignedEvaluation($case);

        $component = Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('محمود علي')
            ->assertSee('P1234567')
            ->assertSee('T-2001')
            ->assertSee('التحقق من هوية العامل')
            ->assertSee('صيانة مكيف هواء')
            ->assertSee('متوسط')
            ->assertSee('تم توقيع النموذج من المقيّم والعامل — النموذج مقفل')
            ->assertSee('أظهر فهماً لأهداف المهمة والغرض منها والعمل على تحقيقها')
            ->assertSee('ممتاز')
            ->assertSee('توليد PDF')
            ->assertDontSee('نموذج التقييم غير مكتمل');

        // All 5 photos rendered through the secure documents route.
        foreach ($case->documents as $document) {
            $component->assertSee(route('documents.show', ['document' => $document]), false);
        }
    }

    public function test_returning_to_practical_shows_signature_wipe_warning(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        $component = Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->set('returnStation', 'practical');

        $component->assertSee('تنبيه');
        $component->assertSee('سيؤدي إلى مسح التوقيعين', false);
        $component->assertSee('إعادة التوقيع بعد الإكمال', false);
        $component->assertSeeHtml("x-show=\"\$wire.returnStation === 'practical'\"");
    }

    public function test_review_does_not_crash_when_evaluation_is_missing(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertOk()
            ->assertSee('نموذج التقييم غير مكتمل')
            ->assertDontSee('تم توقيع النموذج');
    }

    public function test_review_does_not_crash_when_photos_are_missing(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertOk()
            ->assertSee('غير مكتمل')
            ->assertSee('نموذج التقييم غير مكتمل');
    }

    public function test_cannot_view_case_that_is_not_in_review_status(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'غير جاهز',
            'passport_number' => 'P1111111',
            'ticket_number' => 'T-1111',
            'status' => CaseStatus::practical,
        ]);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('الحالات قيد المراجعة')
            ->assertDontSee('غير جاهز');
    }

    public function test_back_to_list_returns_to_queue(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('محمود علي')
            ->call('backToList')
            ->assertSee('الحالات قيد المراجعة')
            ->assertDontSee('التحقق من هوية العامل');
    }

    public function test_document_image_is_served_to_authenticated_user(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $document = $case->documents->first();

        $this->get('/documents/'.$document->id)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_document_image_is_not_served_to_guests(): void
    {
        $this->user = User::factory()->create(['role' => 'review']);
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $document = $case->documents->first();

        $this->get('/documents/'.$document->id)->assertRedirect('/login');
    }

    public function test_document_image_missing_file_returns_404(): void
    {
        $this->actingAsUser();
        $case = $this->createReviewCase();
        $this->storeAllPhotos($case);
        $document = $case->documents->first();

        Storage::disk('local')->delete($document->path);

        $this->get('/documents/'.$document->id)->assertNotFound();
    }
}
