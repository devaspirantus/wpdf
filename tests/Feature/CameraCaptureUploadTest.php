<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\CameraCapture;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CameraCaptureUploadTest extends TestCase
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

    private function capture(): Testable
    {
        return Livewire::test(CameraCapture::class, [
            'caseId' => $this->case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'label' => 'صورة العامل',
        ]);
    }

    public function test_the_component_renders_the_upload_from_device_button(): void
    {
        $this->capture()
            ->assertOk()
            ->assertSee('رفع من الجهاز')
            ->assertSee('التقاط الصورة');
    }

    public function test_uploading_a_valid_jpeg_data_url_stores_the_document(): void
    {
        $fake = UploadedFile::fake()->image('upload.jpg');
        $uploadDataUrl = 'data:image/jpeg;base64,'.base64_encode($fake->get());

        $this->capture()
            ->call('saveImage', $uploadDataUrl)
            ->assertHasNoErrors()
            ->assertDispatched('case-document-stored');

        $this->assertDatabaseHas('documents', [
            'case_id' => $this->case->id,
            'station' => 'reception',
            'type' => 'worker_photo',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_non_image_data_url_is_rejected(): void
    {
        $this->capture()
            ->call('saveImage', 'data:text/plain;base64,SGVsbG8=')
            ->assertHasErrors(['imageData']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_image_larger_than_five_megabytes_is_rejected(): void
    {
        $binary = str_repeat("\0", 5 * 1024 * 1024 + 1024);

        $this->capture()
            ->call('saveImage', 'data:image/jpeg;base64,'.base64_encode($binary))
            ->assertHasErrors(['imageData']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_document_created_by_upload_has_the_same_shape_as_camera_capture(): void
    {
        $fake = UploadedFile::fake()->image('upload.jpg');
        $uploadDataUrl = 'data:image/jpeg;base64,'.base64_encode($fake->get());

        $this->capture()
            ->call('saveImage', $uploadDataUrl)
            ->assertHasNoErrors();

        $cameraDoc = $this->case->documents()->firstWhere('type', 'worker_photo');

        $this->assertNotNull($cameraDoc);
        $this->assertSame('reception', $cameraDoc->station->value);
        $this->assertSame('worker_photo', $cameraDoc->type);
        $this->assertSame($this->user->id, $cameraDoc->created_by);

        Storage::disk('local')->assertExists($cameraDoc->path);

        $name = basename($cameraDoc->path);
        preg_match('#^worker_photo\.(jpg|png)$#', $name, $m);
        $this->assertSame($name, $m[0]);
    }
}
