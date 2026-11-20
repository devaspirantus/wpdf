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

    public function test_capture_without_an_image_fails_validation(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->call('capture')
            ->assertHasErrors(['image' => 'required']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_capture_with_a_valid_image_stores_the_document(): void
    {
        Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ])
            ->set('image', UploadedFile::fake()->image('worker.png'))
            ->call('capture')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'case_id' => $this->case->id,
            'station' => 'reception',
            'type' => 'worker_photo',
            'created_by' => $this->user->id,
        ]);
    }
}
