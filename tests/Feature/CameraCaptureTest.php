<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\CameraCapture;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class CameraCaptureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private WorkerCase $case;

    private string $sampleImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'reception']);
        $this->actingAs($this->user);

        $this->case = WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::reception,
        ]);

        $fake = UploadedFile::fake()->image('worker.jpg');
        $this->sampleImage = 'data:image/jpeg;base64,'.base64_encode($fake->get());
    }

    public function test_the_component_renders_when_no_document_exists_yet(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->assertOk()
            ->assertSee('صورة العامل')
            ->assertSee('التقاط الصورة')
            ->assertDontSee('تم الالتقاط ✓');
    }

    public function test_the_component_shows_a_done_badge_when_a_document_exists(): void
    {
        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            UploadedFile::fake()->image('worker.jpg'),
            $this->user->id,
        );

        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->assertOk()
            ->assertSee('تم الالتقاط ✓');
    }

    public function test_save_image_without_a_data_url_fails_validation(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->call('saveImage', '')
            ->assertHasErrors(['imageData']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_save_image_with_an_invalid_data_url_fails_validation(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->call('saveImage', 'not-a-data-url')
            ->assertHasErrors(['imageData']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_save_image_with_a_valid_data_url_stores_the_document(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->call('saveImage', $this->sampleImage)
            ->assertHasNoErrors()
            ->assertDispatched('case-document-stored');

        $this->assertDatabaseHas('documents', [
            'case_id' => $this->case->id,
            'station' => 'reception',
            'type' => 'worker_photo',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_save_image_replaces_an_existing_document_on_retake(): void
    {
        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            UploadedFile::fake()->image('worker.jpg'),
            $this->user->id,
        );

        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->call('saveImage', $this->sampleImage)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseHas('documents', [
            'case_id' => $this->case->id,
            'station' => 'reception',
            'type' => 'worker_photo',
        ]);
    }
}
