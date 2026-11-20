<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreCaseDocumentTest extends TestCase
{
    use RefreshDatabase;

    private WorkerCase $case;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create();
        $this->case = WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::reception,
        ]);
    }

    public function test_a_document_is_stored_on_the_private_disk_with_the_correct_row(): void
    {
        $file = UploadedFile::fake()->image('worker.jpg');

        $document = app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            $file,
            $this->user->id,
        );

        $path = sprintf('cases/%d/reception/worker_photo.jpg', $this->case->id);

        Storage::disk('local')->assertExists($path);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'case_id' => $this->case->id,
            'station' => 'reception',
            'type' => 'worker_photo',
            'path' => $path,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_a_document_file_is_never_writable_on_the_public_disk(): void
    {
        $file = UploadedFile::fake()->image('passport.jpg');

        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'passport',
            $file,
            $this->user->id,
        );

        Storage::disk('local')->assertMissing('public');
    }

    public function test_replacing_an_existing_document_of_the_same_type_deletes_the_old_file(): void
    {
        $first = UploadedFile::fake()->image('worker.jpg');
        $second = UploadedFile::fake()->image('worker-retake.jpg');

        $document = app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            $first,
            $this->user->id,
        );

        $path = sprintf('cases/%d/reception/worker_photo.jpg', $this->case->id);

        $replaced = app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            $second,
            $this->user->id,
        );

        Storage::disk('local')->assertExists($path);
        $this->assertDatabaseCount('documents', 1);
        $this->assertTrue($document->is($replaced));
        $this->assertSame($path, $replaced->fresh()->path);
    }

    public function test_documents_of_different_types_coexist(): void
    {
        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'worker_photo',
            UploadedFile::fake()->image('worker.jpg'),
            $this->user->id,
        );

        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            'ticket',
            UploadedFile::fake()->image('ticket.jpg'),
            $this->user->id,
        );

        $this->assertDatabaseCount('documents', 2);
    }
}
