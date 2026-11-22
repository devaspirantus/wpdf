<?php

namespace Tests\Feature;

use App\Actions\GenerateCasePdf;
use App\Actions\ReturnCase;
use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\ReviewStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use LogicException;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
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

    private function createCompleteCase(): WorkerCase
    {
        $case = WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-2001',
            'status' => CaseStatus::review,
        ]);

        $stored = app(StoreCaseDocument::class);

        $stored->handle($case, Station::reception, 'worker_photo', UploadedFile::fake()->image('worker.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'passport', UploadedFile::fake()->image('passport.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'ticket', UploadedFile::fake()->image('ticket.png'), $this->user->id);
        $stored->handle($case, Station::theory, 'theory_photo', UploadedFile::fake()->image('theory.png'), $this->user->id);
        $stored->handle($case, Station::practical, 'practical_photo', UploadedFile::fake()->image('practical.png'), $this->user->id);

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

        return $case->fresh()->load(['documents', 'evaluation']);
    }

    public function test_pdf_cannot_be_generated_when_documents_are_missing(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'ناقص',
            'passport_number' => 'P5555555',
            'ticket_number' => 'T-5555',
            'status' => CaseStatus::review,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('لا يمكن توليد الـPDF');

        app(GenerateCasePdf::class)->handle($case);
    }

    public function test_pdf_cannot_be_generated_when_evaluation_is_not_signed(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'غير موقّع',
            'passport_number' => 'P6666666',
            'ticket_number' => 'T-6666',
            'status' => CaseStatus::review,
        ]);

        $stored = app(StoreCaseDocument::class);

        $stored->handle($case, Station::reception, 'worker_photo', UploadedFile::fake()->image('worker.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'passport', UploadedFile::fake()->image('passport.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'ticket', UploadedFile::fake()->image('ticket.png'), $this->user->id);
        $stored->handle($case, Station::theory, 'theory_photo', UploadedFile::fake()->image('theory.png'), $this->user->id);
        $stored->handle($case, Station::practical, 'practical_photo', UploadedFile::fake()->image('practical.png'), $this->user->id);

        $scores = array_fill(1, 13, 3);
        $scores[1] = 5;

        app(SaveEvaluation::class)->handle(
            $case,
            'صيانة مكيف هواء',
            Difficulty::medium,
            $scores,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('لا يمكن توليد الـPDF');

        app(GenerateCasePdf::class)->handle($case);
    }

    public function test_complete_case_generates_a_valid_pdf_with_expected_filename(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        $relative = app(GenerateCasePdf::class)->handle($case);

        $absolute = Storage::disk('local')->path($relative);

        $this->assertFileExists($absolute);
        $this->assertSame('%PDF-', substr(file_get_contents($absolute, false, null, 0), 0, 5));
        $this->assertLessThan(10 * 1024 * 1024, filesize($absolute));

        $expectedName = $case->ticket_number.'_'.now()->format('Y-m-d').'_v1.pdf';
        $this->assertStringEndsWith($expectedName, $relative);
    }

    public function test_generate_pdf_button_shows_error_when_incomplete(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'ناقص',
            'passport_number' => 'P7777777',
            'ticket_number' => 'T-7777',
            'status' => CaseStatus::review,
        ]);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->call('generatePdf')
            ->assertSee('لا يمكن توليد الـPDF', false)
            ->assertDontSee('تحميل PDF')
            ->assertSee('توليد PDF');
    }

    public function test_generating_after_return_to_practical_requires_new_signatures(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        app(GenerateCasePdf::class)->handle($case);

        app(ReturnCase::class)->handle($case, Station::practical, 'أعد التوقيع', $this->user->id);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('لا يمكن توليد الـPDF');

        app(GenerateCasePdf::class)->handle($case->fresh());
    }

    public function test_download_route_streams_the_latest_pdf(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        app(GenerateCasePdf::class)->handle($case);

        $this->get(route('pdf.download', $case))->assertOk();
    }

    public function test_download_route_returns_404_when_no_pdf_exists(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        $this->get(route('pdf.download', $case))->assertNotFound();
    }

    public function test_download_route_is_protected_from_guests(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();
        $this->app['auth']->forgetGuards();

        $this->get(route('pdf.download', $case))->assertRedirect('/login');
    }
}
