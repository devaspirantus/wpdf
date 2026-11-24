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

    /** @var array<int, string> */
    private array $tempFixtures = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFixtures as $tmp) {
            @unlink($tmp);
        }

        parent::tearDown();
    }

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

        $stored->handle($case, Station::reception, 'worker_photo', $this->brightImageFile('worker.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'passport', $this->brightImageFile('passport.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'ticket', $this->brightImageFile('ticket.png'), $this->user->id);
        $stored->handle($case, Station::theory, 'theory_photo', $this->brightImageFile('theory.png'), $this->user->id);
        $stored->handle($case, Station::practical, 'practical_photo', $this->brightImageFile('practical.png'), $this->user->id);

        $scores = array_fill(1, 13, 3);
        $scores[1] = 5;

        app(SaveEvaluation::class)->handle(
            $case,
            'صيانة مكيف هواء',
            Difficulty::medium,
            $scores,
        );

        $signature = 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('signature.png')->get());
        app(SignEvaluation::class)->handle($case, $signature, $signature);

        return $case->fresh()->load(['documents', 'evaluation']);
    }

    private function brightImageFile(string $name): UploadedFile
    {
        $img = imagecreatetruecolor(640, 480);
        $background = imagecolorallocate($img, 240, 240, 245);
        $red = imagecolorallocate($img, 200, 30, 30);
        imagefill($img, 0, 0, $background);
        imagefilledrectangle($img, 120, 90, 520, 390, $red);

        $tmp = tempnam(sys_get_temp_dir(), 'wpdf-fix-').'.jpg';
        imagejpeg($img, $tmp, 90);
        imagedestroy($img);

        $this->tempFixtures[] = $tmp;

        return new UploadedFile($tmp, $name, 'image/jpeg', null, true);
    }

    private function assertPdfEmbedsBrightImage(string $path): void
    {
        $content = file_get_contents($path);
        $this->assertTrue(
            preg_match_all('/\xFF\xD8\xFF.*?\xFF\xD9/s', $content, $matches) > 0,
            'No JPEG (DCT) image found inside the PDF.',
        );

        $jpeg = '';
        foreach ($matches[0] as $candidate) {
            if (strlen($candidate) > strlen($jpeg)) {
                $jpeg = $candidate;
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'wpdf-pdf-').'.jpg';
        file_put_contents($tmp, $jpeg);
        $this->tempFixtures[] = $tmp;

        $decoded = @imagecreatefromjpeg($tmp);
        $this->assertTrue($decoded !== false, 'Embedded JPEG could not be decoded.');

        $pixel = imagecolorat($decoded, 320, 240);
        imagedestroy($decoded);
        $this->assertGreaterThan(120, ($pixel >> 16) & 0xFF, 'Center pixel R too low (image may be black)');
        $this->assertLessThan(120, ($pixel >> 8) & 0xFF, 'Center pixel G too high (image may be black)');
        $this->assertLessThan(120, $pixel & 0xFF, 'Center pixel B too high (image may be black)');
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

        $stored->handle($case, Station::reception, 'worker_photo', $this->brightImageFile('worker.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'passport', $this->brightImageFile('passport.png'), $this->user->id);
        $stored->handle($case, Station::reception, 'ticket', $this->brightImageFile('ticket.png'), $this->user->id);
        $stored->handle($case, Station::theory, 'theory_photo', $this->brightImageFile('theory.png'), $this->user->id);
        $stored->handle($case, Station::practical, 'practical_photo', $this->brightImageFile('practical.png'), $this->user->id);

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
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', file_get_contents($absolute)));

        $expectedName = $case->ticket_number.'_'.now()->format('Y-m-d').'_v1.pdf';
        $this->assertStringEndsWith($expectedName, $relative);

        $this->assertPdfEmbedsBrightImage($absolute);
    }

    public function test_long_worker_details_and_task_fit_in_two_pages(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();
        $case->update(['full_name' => str_repeat('محمد ', 50)]);
        $case->evaluation()->update(['task' => str_repeat('صيانة ', 42)]);

        $relative = app(GenerateCasePdf::class)->handle($case->fresh()->load(['documents', 'evaluation']));

        $content = Storage::disk('local')->get($relative);
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $content));
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

    public function test_print_route_serves_the_pdf_inline(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        app(GenerateCasePdf::class)->handle($case);

        $response = $this->get(route('pdf.print', $case));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $response->assertSee('%PDF-', false);
    }

    public function test_print_route_returns_404_when_no_pdf_exists(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        $this->get(route('pdf.print', $case))->assertNotFound();
    }

    public function test_print_route_is_protected_from_guests(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();
        $this->app['auth']->forgetGuards();

        $this->get(route('pdf.print', $case))->assertRedirect('/login');
    }

    public function test_review_station_shows_print_link_after_generation(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->call('generatePdf')
            ->assertSee('تحميل PDF', false)
            ->assertSee('طباعة PDF', false)
            ->call('backToList')
            ->assertSee('الحالات المكتملة وملفات PDF')
            ->assertSee($case->full_name)
            ->assertSee($case->ticket_number);

    }

    public function test_review_station_detects_existing_pdf_without_generation(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        app(GenerateCasePdf::class)->handle($case);

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertSee('تحميل PDF', false)
            ->assertSee('طباعة PDF', false);
    }

    public function test_review_station_hides_pdf_links_when_no_pdf_exists(): void
    {
        $this->actingAsUser();
        $case = $this->createCompleteCase();

        Livewire::test(ReviewStation::class)
            ->call('selectCase', $case->id)
            ->assertDontSee('تحميل PDF', false)
            ->assertDontSee('طباعة PDF', false);
    }
}
