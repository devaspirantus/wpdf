<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Models\CaseReturn;
use App\Models\Document;
use App\Models\Evaluation;
use App\Models\StationVisit;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_five_tables_exist_with_their_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('worker_cases'));
        $this->assertTrue(Schema::hasTable('documents'));
        $this->assertTrue(Schema::hasTable('evaluations'));
        $this->assertTrue(Schema::hasTable('station_visits'));
        $this->assertTrue(Schema::hasTable('case_returns'));

        foreach (['full_name', 'passport_number', 'ticket_number', 'status'] as $column) {
            $this->assertTrue(Schema::hasColumn('worker_cases', $column));
        }
        foreach (['case_id', 'station', 'type', 'path', 'created_by', 'created_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('documents', $column));
        }
        foreach (['case_id', 'task', 'difficulty', 'scores', 'evaluator_name', 'evaluator_id_number', 'evaluator_signature', 'worker_signature', 'signed_at', 'created_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('evaluations', $column));
        }
        foreach (['case_id', 'station', 'user_id', 'started_at', 'completed_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('station_visits', $column));
        }
        foreach (['case_id', 'returned_to_station', 'note', 'returned_by', 'created_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('case_returns', $column));
        }
    }

    public function test_minimal_records_in_all_tables_link_through_their_relationships(): void
    {
        $user = User::factory()->create(['role' => 'review']);
        $case = WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-0001',
            'status' => CaseStatus::reception,
        ]);

        $document = Document::create([
            'case_id' => $case->id,
            'station' => Station::reception,
            'type' => 'worker_photo',
            'path' => 'private/workers/T-0001/photo.png',
            'created_by' => $user->id,
        ]);

        $evaluation = Evaluation::create([
            'case_id' => $case->id,
            'task' => 'Task',
            'difficulty' => Difficulty::medium,
            'scores' => array_fill(0, 13, 3),
            'evaluator_name' => 'Evaluator',
            'evaluator_id_number' => 'ID-1',
            'evaluator_signature' => 'sig-evaluator',
            'worker_signature' => 'sig-worker',
        ]);

        $visit = StationVisit::create([
            'case_id' => $case->id,
            'station' => Station::reception,
            'user_id' => $user->id,
        ]);

        $return = CaseReturn::create([
            'case_id' => $case->id,
            'returned_to_station' => Station::theory,
            'note' => 'ملاحظة',
            'returned_by' => $user->id,
        ]);

        $this->assertSame($case->id, $document->workerCase->id);
        $this->assertSame($user->id, $document->creator->id);
        $this->assertSame($case->id, $evaluation->workerCase->id);
        $this->assertSame($case->id, $visit->workerCase->id);
        $this->assertSame($user->id, $visit->user->id);
        $this->assertSame($case->id, $return->workerCase->id);
        $this->assertSame($user->id, $return->returnedBy->id);

        $freshCase = $case->fresh()->loadMissing('documents', 'evaluation', 'stationVisits', 'returns');
        $this->assertCount(1, $freshCase->documents);
        $this->assertNotNull($freshCase->evaluation);
        $this->assertCount(1, $freshCase->stationVisits);
        $this->assertCount(1, $freshCase->returns);
    }

    public function test_duplicate_ticket_number_is_rejected(): void
    {
        WorkerCase::create([
            'full_name' => 'أحمد',
            'passport_number' => 'P1111111',
            'ticket_number' => 'T-DUP-1',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        WorkerCase::create([
            'full_name' => 'محمد',
            'passport_number' => 'P2222222',
            'ticket_number' => 'T-DUP-1',
        ]);
    }
}
