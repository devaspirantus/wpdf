<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Livewire\PracticalStation;
use App\Models\User;
use App\Models\WorkerCase;
use App\Services\StationCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticalSignatureTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

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
            'ticket_number' => 'T-3002',
            'status' => CaseStatus::practical,
        ]);
    }

    private function validScores(int $value = 3): array
    {
        $scores = [];
        foreach (SaveEvaluation::SECTIONS as $criteria) {
            foreach (array_keys($criteria) as $key) {
                $scores[$key] = $value;
            }
        }

        return $scores;
    }

    private function saveEvaluation(WorkerCase $case): WorkerCase
    {
        app(SaveEvaluation::class)->handle($case, 'مهمة للتوقيع', Difficulty::easy, $this->validScores(3));

        return $case->fresh();
    }

    public function test_dual_signatures_sign_the_evaluation_and_lock_the_form(): void
    {
        $this->actingAsUser();
        $case = $this->saveEvaluation($this->createPracticalCase());

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('evaluatorSignature', self::PNG_SIGNATURE)
            ->set('workerSignature', self::PNG_SIGNATURE)
            ->call('signEvaluation')
            ->assertHasNoErrors();

        $this->assertTrue($component->instance()->isSigned);
        $this->assertNotNull($component->instance()->signedAt);

        $evaluation = $case->evaluation()->first();
        $this->assertNotNull($evaluation->signed_at);
        $this->assertSame(self::PNG_SIGNATURE, $evaluation->evaluator_signature);
        $this->assertSame(self::PNG_SIGNATURE, $evaluation->worker_signature);

        $component
            ->set('task', 'مهمة معدلة بعد التوقيع')
            ->call('saveEvaluation')
            ->assertHasErrors(['scores']);

        $this->assertSame('مهمة للتوقيع', $case->evaluation()->first()->task);
    }

    public function test_signing_twice_is_rejected(): void
    {
        $this->actingAsUser();
        $case = $this->saveEvaluation($this->createPracticalCase());

        app(SignEvaluation::class)->handle($case, self::PNG_SIGNATURE, self::PNG_SIGNATURE);
        $case = $case->fresh();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->assertSet('isSigned', true)
            ->set('evaluatorSignature', self::PNG_SIGNATURE)
            ->set('workerSignature', self::PNG_SIGNATURE)
            ->call('signEvaluation')
            ->assertHasErrors(['evaluator_signature']);

        $evaluation = $case->evaluation()->first();
        $this->assertSame(self::PNG_SIGNATURE, $evaluation->evaluator_signature);
    }

    public function test_signing_without_a_worker_signature_is_rejected(): void
    {
        $this->actingAsUser();
        $case = $this->saveEvaluation($this->createPracticalCase());

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('evaluatorSignature', self::PNG_SIGNATURE)
            ->set('workerSignature', '')
            ->call('signEvaluation')
            ->assertHasErrors(['worker_signature']);

        $this->assertNull($case->evaluation()->first()->signed_at);
    }

    public function test_an_invalid_signature_payload_is_rejected(): void
    {
        $this->actingAsUser();
        $case = $this->saveEvaluation($this->createPracticalCase());

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('evaluatorSignature', 'this is not a data url')
            ->set('workerSignature', self::PNG_SIGNATURE)
            ->call('signEvaluation')
            ->assertHasErrors(['evaluator_signature']);

        $this->assertNull($case->evaluation()->first()->signed_at);
    }

    public function test_signed_evaluation_satisfies_the_station_signatures_requirement(): void
    {
        $this->actingAsUser();
        $case = $this->saveEvaluation($this->createPracticalCase());

        app(SignEvaluation::class)->handle($case, self::PNG_SIGNATURE, self::PNG_SIGNATURE);
        $case = $case->fresh();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->assertSet('signedAt', $case->evaluation->signed_at->format('Y-m-d H:i'));

        $completion = app(StationCompletion::class);
        $requirements = collect($completion->requirements($case, Station::practical));

        $this->assertTrue($requirements->whereIn('label', ['التوقيعان على النموذج'])->value('met'));
    }
}
