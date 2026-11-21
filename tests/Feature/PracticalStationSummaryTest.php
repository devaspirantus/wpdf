<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\PracticalStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PracticalStationSummaryTest extends TestCase
{
    use RefreshDatabase;

    public const PNG_SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private User $user;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['role' => 'practical']);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    private function createPracticalCase(): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => 'سعيد حسن',
            'passport_number' => 'P7654321',
            'ticket_number' => 'T-3001',
            'status' => CaseStatus::practical,
        ]);
    }

    private function storePracticalPhoto(WorkerCase $case): void
    {
        app(StoreCaseDocument::class)->handle(
            $case,
            Station::practical,
            'practical_photo',
            UploadedFile::fake()->image('practical.png'),
            $this->user->id,
        );
    }

    private function saveEvaluation(WorkerCase $case): void
    {
        $scores = [];
        foreach (SaveEvaluation::SECTIONS as $criteria) {
            foreach (array_keys($criteria) as $key) {
                $scores[$key] = 3;
            }
        }

        app(SaveEvaluation::class)->handle($case, 'تصليح المضخة الرئيسية', Difficulty::medium, $scores);
    }

    private function signEvaluation(WorkerCase $case): void
    {
        app(SignEvaluation::class)->handle($case, self::PNG_SIGNATURE, self::PNG_SIGNATURE);
    }

    public function test_practical_summary_shows_station_name_role_and_missing_state(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $component = Livewire::test(PracticalStation::class, ['case' => $case]);

        $component->assertSee('ملخص نهاية المحطة')
            ->assertSee('المختبر العملي')
            ->assertSee('المعمل العملي')
            ->assertSee('0 / 3')
            ->assertSee('ناقص');
    }

    public function test_practical_summary_shows_ready_state_with_next_station(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $this->storePracticalPhoto($case);
        $this->saveEvaluation($case);
        $this->signEvaluation($case);

        $component = Livewire::test(PracticalStation::class, ['case' => $case]);

        $component->assertSee('3 / 3')
            ->assertSee('جاهز للمحطة التالية')
            ->assertSee('المحطة التالية: مراجعة البيانات');
    }
}
