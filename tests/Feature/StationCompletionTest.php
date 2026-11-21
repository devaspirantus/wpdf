<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Models\User;
use App\Models\WorkerCase;
use App\Services\StationCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StationCompletionTest extends TestCase
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

    public function test_a_fresh_reception_case_is_incomplete_and_reports_all_six_requirements(): void
    {
        $completion = app(StationCompletion::class);

        $this->assertFalse($completion->isComplete($this->case, Station::reception));
        $this->assertSame(
            ['صورة العامل', 'صورة جواز السفر', 'صورة التذكرة'],
            $completion->missing($this->case, Station::reception),
        );

        $requirements = $completion->requirements($this->case, Station::reception);

        $this->assertCount(6, $requirements);
        $this->assertSame(
            ['اسم العامل', 'رقم الجواز', 'رقم التذكرة', 'صورة العامل', 'صورة جواز السفر', 'صورة التذكرة'],
            array_column($requirements, 'label'),
        );
        $this->assertSame(
            [true, true, true, false, false, false],
            array_column($requirements, 'met'),
        );
    }

    public function test_a_reception_case_with_all_three_documents_is_complete(): void
    {
        $store = app(StoreCaseDocument::class);

        foreach (['worker_photo', 'passport', 'ticket'] as $type) {
            $store->handle($this->case, Station::reception, $type, UploadedFile::fake()->image('doc.png'), $this->user->id);
        }

        $completion = app(StationCompletion::class);

        $this->assertTrue($completion->isComplete($this->case, Station::reception));
        $this->assertSame([], $completion->missing($this->case, Station::reception));
    }

    public function test_documents_from_other_stations_do_not_count_towards_reception(): void
    {
        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::theory,
            'worker_photo',
            UploadedFile::fake()->image('other.png'),
            $this->user->id,
        );

        $completion = app(StationCompletion::class);

        $this->assertSame(
            ['صورة العامل', 'صورة جواز السفر', 'صورة التذكرة'],
            $completion->missing($this->case, Station::reception),
        );
    }

    public function test_theory_requires_the_theory_photo(): void
    {
        $completion = app(StationCompletion::class);

        $this->assertFalse($completion->isComplete($this->case, Station::theory));
        $this->assertSame(['صورة الاختبار النظري'], $completion->missing($this->case, Station::theory));

        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::theory,
            'theory_photo',
            UploadedFile::fake()->image('theory.png'),
            $this->user->id,
        );

        $this->assertTrue($completion->isComplete($this->case, Station::theory));
    }

    public function test_practical_requires_the_practical_photo_and_the_evaluation(): void
    {
        $completion = app(StationCompletion::class);

        $this->assertSame(['صورة الاختبار العملي', 'نموذج التقييم'], $completion->missing($this->case, Station::practical));

        app(StoreCaseDocument::class)->handle(
            $this->case,
            Station::practical,
            'practical_photo',
            UploadedFile::fake()->image('practical.png'),
            $this->user->id,
        );

        $this->assertSame(['نموذج التقييم'], $completion->missing($this->case, Station::practical));

        $this->saveEvaluation();

        $this->assertTrue($completion->isComplete($this->case, Station::practical));
    }

    private function saveEvaluation(): void
    {
        $this->actingAs($this->user);

        $scores = [];
        foreach (SaveEvaluation::SECTIONS as $criteria) {
            foreach (array_keys($criteria) as $key) {
                $scores[$key] = 3;
            }
        }

        app(SaveEvaluation::class)->handle(
            $this->case,
            'تصليح المضخة الرئيسية',
            Difficulty::medium,
            $scores,
        );
    }
}
