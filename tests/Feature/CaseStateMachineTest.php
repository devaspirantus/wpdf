<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\User;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use LogicException;
use Tests\TestCase;

class CaseStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private WorkerCase $case;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->case = WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-1001',
            'status' => CaseStatus::reception,
        ]);
    }

    private function storeReceptionDocuments(): void
    {
        $store = app(StoreCaseDocument::class);

        foreach (['worker_photo', 'passport', 'ticket'] as $type) {
            $store->handle($this->case, Station::reception, $type, UploadedFile::fake()->image('doc.png'), $this->user->id);
        }
    }

    public function test_advance_moves_a_complete_reception_case_to_theory(): void
    {
        $this->storeReceptionDocuments();

        $advanced = app(CaseStateMachine::class)->advance($this->case);

        $this->assertSame(CaseStatus::theory, $advanced->status);
        $this->assertDatabaseHas('worker_cases', [
            'id' => $this->case->id,
            'status' => CaseStatus::theory->value,
        ]);
    }

    public function test_advance_is_rejected_server_side_when_documents_are_missing(): void
    {
        $this->storeReceptionDocument('worker_photo');
        $this->storeReceptionDocument('passport');

        try {
            app(CaseStateMachine::class)->advance($this->case);
            $this->fail('IncompleteStationException was not thrown');
        } catch (IncompleteStationException $e) {
            $this->assertSame($this->case->id, $e->case->id);
            $this->assertSame(Station::reception, $e->station);
            $this->assertSame(['صورة التذكرة'], $e->missing);
            $this->assertSame('المحطة الاستقبال غير مكتملة: صورة التذكرة', $e->getMessage());
        }

        $this->assertSame(CaseStatus::reception, $this->case->fresh()->status);
    }

    public function test_advance_rejects_a_case_without_status(): void
    {
        $this->case->update(['status' => null]);

        $this->expectException(LogicException::class);

        app(CaseStateMachine::class)->advance($this->case);
    }

    public function test_advance_rejects_a_terminal_case(): void
    {
        $this->case->update(['status' => CaseStatus::completed]);

        $this->expectException(LogicException::class);

        app(CaseStateMachine::class)->advance($this->case);
    }

    public function test_next_station_returns_the_correct_status_for_each_station(): void
    {
        $machine = app(CaseStateMachine::class);

        $this->assertSame(CaseStatus::theory, $machine->nextStation(Station::reception));
        $this->assertSame(CaseStatus::practical, $machine->nextStation(Station::theory));
        $this->assertSame(CaseStatus::review, $machine->nextStation(Station::practical));

        $this->expectException(LogicException::class);

        $machine->nextStation(Station::review);
    }

    private function storeReceptionDocument(string $type): void
    {
        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::reception,
            $type,
            UploadedFile::fake()->image($type.'.png'),
            $this->user->id,
        );
    }
}
