<?php

namespace Tests\Feature;

use App\Actions\SaveEvaluation;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Livewire\PracticalStation;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticalEvaluationTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_a_practitioner_can_save_an_evaluation_via_the_form(): void
    {
        $user = $this->actingAsUser();
        $case = $this->createPracticalCase();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('task', 'تصليح المضخة الرئيسية')
            ->set('difficulty', 'medium')
            ->set('scores', $this->validScores(4))
            ->call('saveEvaluation')
            ->assertHasNoErrors();

        $this->assertTrue($component->instance()->evaluationSaved);
        $this->assertDatabaseCount('evaluations', 1);
        $this->assertDatabaseHas('evaluations', [
            'case_id' => $case->id,
            'task' => 'تصليح المضخة الرئيسية',
            'difficulty' => Difficulty::medium->value,
            'evaluator_name' => $user->name,
            'evaluator_id_number' => $user->email,
        ]);

        $evaluation = $case->evaluation()->first();
        $this->assertSame($this->validScores(4), $evaluation->scores);
        $this->assertNull($evaluation->signed_at);
    }

    public function test_saving_an_existing_evaluation_updates_it(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $initial = app(SaveEvaluation::class)->handle($case, 'مهمة أولى', Difficulty::easy, $this->validScores(1));
        $this->assertNotEmpty($initial->id);
        $case = $case->fresh();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->assertSet('task', 'مهمة أولى')
            ->assertSet('difficulty', 'easy')
            ->set('task', 'مهمة ثانية')
            ->set('difficulty', 'hard')
            ->set('scores', $this->validScores(5))
            ->call('saveEvaluation')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('evaluations', 1);
        $this->assertDatabaseHas('evaluations', [
            'case_id' => $case->id,
            'task' => 'مهمة ثانية',
            'difficulty' => Difficulty::hard->value,
        ]);
        $this->assertSame($this->validScores(5), $case->evaluation()->first()->scores);
    }

    public function test_saving_a_blank_task_is_rejected_with_an_arabic_error(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('task', '')
            ->set('scores', $this->validScores())
            ->call('saveEvaluation')
            ->assertHasErrors(['task']);

        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_an_out_of_scale_score_is_rejected(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $scores = $this->validScores();
        $scores[1] = 7;

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('task', 'تصليح المضخة الرئيسية')
            ->set('scores', $scores)
            ->call('saveEvaluation')
            ->assertHasErrors(['scores.1']);

        $this->assertDatabaseCount('evaluations', 0);
    }

    public function test_a_signed_evaluation_is_locked(): void
    {
        $this->actingAsUser();
        $case = $this->createPracticalCase();

        $signed = Evaluation::create([
            'case_id' => $case->id,
            'task' => 'مهمة موقعة',
            'difficulty' => Difficulty::medium->value,
            'scores' => $this->validScores(3),
            'evaluator_name' => 'مقيّم',
            'evaluator_id_number' => 'eval@example.com',
            'evaluator_signature' => 'sig',
            'worker_signature' => 'sig',
            'signed_at' => now(),
        ]);

        $component = Livewire::test(PracticalStation::class, ['case' => $case])
            ->set('task', 'مهمة معدلة')
            ->set('scores', $this->validScores(2))
            ->call('saveEvaluation')
            ->assertHasErrors(['scores']);

        $fresh = Evaluation::find($signed->id);
        $this->assertSame('مهمة موقعة', $fresh->task);
        $this->assertSame($this->validScores(3), $fresh->scores);
    }
}
