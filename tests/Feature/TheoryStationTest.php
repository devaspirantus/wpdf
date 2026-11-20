<?php

namespace Tests\Feature;

use App\Actions\StoreCaseDocument;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Livewire\CaseSearch;
use App\Livewire\TheoryStation;
use App\Models\User;
use App\Models\WorkerCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class TheoryStationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['role' => 'reception']);
        $this->actingAs($user);
        $this->user = $user;

        return $user;
    }

    private function createTheoryCase(): WorkerCase
    {
        return WorkerCase::create([
            'full_name' => 'محمود علي',
            'passport_number' => 'P1234567',
            'ticket_number' => 'T-2001',
            'status' => CaseStatus::theory,
        ]);
    }

    private function storeTheoryPhoto(WorkerCase $case): void
    {
        app(StoreCaseDocument::class)->handle(
            $case,
            Station::theory,
            'theory_photo',
            UploadedFile::fake()->image('theory.png'),
            $this->user->id,
        );
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $case = $this->createTheoryCase();

        $this->get('/theory/'.$case->id)->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_the_theory_screen(): void
    {
        $this->actingAsUser();
        $case = $this->createTheoryCase();

        $this->get('/theory/'.$case->id)
            ->assertOk()
            ->assertSee('المختبر النظري')
            ->assertSee('محمود علي')
            ->assertSee('P1234567')
            ->assertSee('T-2001');
    }

    public function test_the_advance_button_is_disabled_until_the_photo_is_stored(): void
    {
        $this->actingAsUser();
        $case = $this->createTheoryCase();

        $component = Livewire::test(TheoryStation::class, ['case' => $case]);

        $component->assertSeeHtml('wire:click="advance" disabled');

        $this->storeTheoryPhoto($case);
        $component->dispatch('case-document-stored', caseId: $case->id);

        $component->assertDontSeeHtml('wire:click="advance" disabled');
    }

    public function test_advance_moves_a_complete_case_to_practical(): void
    {
        $this->actingAsUser();
        $case = $this->createTheoryCase();

        $this->storeTheoryPhoto($case);

        $component = Livewire::test(TheoryStation::class, ['case' => $case])
            ->call('advance');

        $this->assertDatabaseHas('worker_cases', [
            'id' => $case->id,
            'status' => CaseStatus::practical->value,
        ]);
        $this->assertTrue($component->instance()->advancedTo);
    }

    public function test_advance_is_blocked_server_side_and_shows_arabic_error(): void
    {
        $this->actingAsUser();
        $case = $this->createTheoryCase();

        $component = Livewire::test(TheoryStation::class, ['case' => $case])
            ->call('advance')
            ->assertSee('المحطة المختبر النظري غير مكتملة: صورة الاختبار النظري');

        $this->assertSame(
            ['صورة الاختبار النظري'],
            $component->instance()->missingRequirements,
        );
        $this->assertDatabaseHas('worker_cases', [
            'id' => $case->id,
            'status' => CaseStatus::theory->value,
        ]);
    }

    public function test_case_search_shows_theory_link_for_theory_cases(): void
    {
        $this->actingAsUser();
        $case = $this->createTheoryCase();

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', $case->ticket_number)
            ->call('search')
            ->assertSee('الانتقال إلى المختبر النظري');
    }

    public function test_case_search_hides_theory_link_for_other_statuses(): void
    {
        $this->actingAsUser();
        $case = WorkerCase::create([
            'full_name' => 'أحمد',
            'passport_number' => 'P9999999',
            'ticket_number' => 'T-2002',
            'status' => CaseStatus::reception,
        ]);

        Livewire::test(CaseSearch::class)
            ->set('ticketNumber', $case->ticket_number)
            ->call('search')
            ->assertDontSee('الانتقال إلى المختبر النظري');
    }
}
